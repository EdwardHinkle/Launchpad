<?php
define('VISIT_THRESHOLD_MINUTES', 95);

class DHCPPresence {
	private $_handle = false;
	
	public function startWatchingLog() {
		if($this->_handle) {
			pclose($this->_handle);
		}
		
		$this->debug("Opening log file");
		$this->_handle = popen("tail -f /var/log/messages", 'r');
		#$this->_handle = fopen('php://stdin', 'r');
		
		while(!feof($this->_handle)) {
			$line = fgets($this->_handle);
			
			if(preg_match('/(?P<date>[a-z]{3}[ ]{1,2}\d{1,2} \d{2}:\d{2}:\d{2}).+DHCPDISCOVER from(?P<ip>) (?P<mac>[0-9a-f:]{17}) (?:\((?P<hostname>[^\)]+)\) )?via eth0/i', $line, $match)
				|| preg_match('/(?P<date>[a-z]{3}[ ]{1,2}\d{1,2} \d{2}:\d{2}:\d{2}).+DHCP(?:REQUEST|OFFER|ACK) (?:for|on) (?P<ip>[0-9\.]+) (?:from|to) (?P<mac>[0-9a-f:]{17}) (?:\((?P<hostname>[^\)]+)\) )?via eth0/i', $line, $match)) {
				$this->deviceWasSeen($match['mac'], date('Y-m-d H:i:s', strtotime($match['date'])), k($match, 'hostname'), k($match, 'ip'));
			}
		}
		pclose($this->_handle);
	}
        
	public function greeting($name, $short=FALSE) {
		if($short) {
			$greetings[] = $this->greetingTimeOfDay() . ' ' . $name . '. ';
			$greetings[] = 'Hi again ' . $name . '.';
			$greetings[] = 'Welcome ' . $name . '.';
		} else {
			$greetings[] = 'Welcome to the Launchpad, ' . $name;
			$greetings[] = $this->greetingTimeOfDay() . ' ' . $name . ', welcome to the Launchpad.';
			$greetings[] = $this->greetingTimeOfDay() . ' ' . $name . ', what can I do for you today?';
		}
		return $greetings[array_rand($greetings)];
	}
        
	public function greetingLastVisit($ago) {
		$greetings[] = 'You were last here ' . $ago;
		$greetings[] = 'I haven\'t seen you since ' . $ago;
		return $greetings[array_rand($greetings)];
	}
	
	public function greetingTimeOfDay() {
		if(date('H') >= 6 && date('H') <= 11)
			return 'Good morning';
		elseif(date('H') >= 12 && date('H') <= 16)
			return 'Good afternoon';
		elseif(date('H') >= 17 && date('H') <= 21)
			return 'Good evening';
		else
			return 'Hello';
	}
	
	public function deviceWasSeen($mac, $date, $hostname='', $ip='') {
		// Insert or update the record in the main `dns` table
		$record = $this->recordDevice($mac, $date, $hostname, $ip);
		
		$result = array(
			'newDeviceVisit' => FALSE,
			'newUserVisit' => FALSE
		);
		
		$this->debug("mac: $mac date: $date hostname: $hostname");
		$this->debug($record);
		$this->debug();
		
		
		$result['newDeviceVisit'] = $this->processVisit('device_visits', 'device_id', $record['device_id'], $date);
		if($record['user_id'])
			$result['newUserVisit'] = $this->processVisit('user_visits', 'user_id', $record['user_id'], $date);
	
		if($result['newUserVisit']) {
			$user = db()->prepare('SELECT * FROM users WHERE id = :id');
			$user->bindParam(':id', $record['user_id']);
			$user->execute();
			if($user = $user->fetch()) {
	
				$previousVisit = $this->getPreviousVisit($record['user_id'], $result['newUserVisit']);
				if($previousVisit) {
					$greeting = $this->greeting($user['name'], TRUE);
					$greeting .= ' ' . $this->greetingLastVisit($previousVisit);
				} else {
					$greeting = $this->greeting($user['name']);
				}
			
				bot()->Send('2 minutes until !say ' . $greeting);
			}
		}
	
		return $result;
	}
	
	// Returns TRUE if it recorded a new visit
	public function processVisit($table, $col, $record_id, $date) {
		// Get the last visit
		$lastVisit = db()->prepare('SELECT * FROM ' . $table . ' WHERE ' . $col . ' = :id ORDER BY date_lastseen DESC LIMIT 1');
		$lastVisit->bindParam(':id', $record_id);
		$lastVisit->execute();
		$lastVisit = $lastVisit->fetch(PDO::FETCH_ASSOC);
	
		// Insert a new record if it has never been seen before or if the last visit was more than 15 minutes since the last
		if(!$lastVisit || strtotime($lastVisit['date_lastseen']) < (strtotime($date) - (VISIT_THRESHOLD_MINUTES*60)) ) {
			$this->debug("[$table] Inserting new visit for: $record_id");
			$this->debug(strtotime($lastVisit['date_lastseen']));
			$this->debug((strtotime($date) - (VISIT_THRESHOLD_MINUTES*60)));
			$insert = db()->prepare('INSERT INTO ' . $table . ' (' . $col . ', date_entered, date_lastseen) VALUES (:id, :date, :date)');
			$insert->bindParam(':id', $record_id);
			$insert->bindParam(':date', $date);
			$insert->execute();
			return db()->lastInsertId();
		}
		// Otherwise, the device was seen less than 15 minutes ago, so update the lastseen date
		else {
	 		$this->debug("[$table] Already here");
			$update = db()->prepare('UPDATE ' . $table . ' SET date_lastseen = :date WHERE id = :id');
			$update->bindParam(':date', $date);
			$update->bindParam(':id', $lastVisit['id']);
			$update->execute();
			return FALSE;
		}
	}
	
	
	public function getPreviousVisit($user_id, $visit_id) {
		$previous = db()->prepare('SELECT * FROM user_visits
			WHERE user_id = :user_id
				AND id != :visit_id
			ORDER BY date_lastseen DESC LIMIT 1');
		$previous->bindParam(':user_id', $user_id);
		$previous->bindParam(':visit_id', $visit_id);
		$previous->execute();
		if($previous = $previous->fetch()) {
			return Grammar::timeAgoInWords($previous['date_lastseen']);
		}
		else
			return FALSE;
	}
	
	
	public function recordDevice($mac, $date, $hostname='', $ip='') {
		// Check if it exists in the DB already
		$query = db()->prepare('SELECT * FROM dns WHERE mac = :mac');
		$query->bindParam(':mac', $mac);
		$query->execute();
		$record = $query->fetch();
	
		if($record) {
			$update = db()->prepare('UPDATE dns 
				SET date_lastseen = :date ' 
					. ($hostname ? ', client_hostname = :hostname ' : '')
					. ($ip ? ', dhcp_ip = :ip ' : '')
				. 'WHERE mac = :mac AND date_lastseen < :date');
			$update->bindParam(':date', $date);
			$update->bindParam(':mac', $mac);
			if($hostname)
				$update->bindParam(':hostname', $hostname);
			if($ip)
				$update->bindParam(':ip', $ip);
			$update->execute();
			$user_id = $record['user_id'];
			$device_id = $record['id'];
		} else {
			$insert = db()->prepare('INSERT INTO dns (mac, client_hostname, date_lastseen, dhcp_ip) VALUES(:mac, :hostname, :date, :ip)');
			$insert->bindParam(':mac', $mac);
			$insert->bindParam(':hostname', $hostname);
			$insert->bindParam(':date', $date);
			$insert->bindParam(':ip', $ip);
			$insert->execute();
			$user_id = FALSE;
			$device_id = db()->lastInsertId();
		}
		
		return array('user_id'=>$user_id, 'device_id'=>$device_id);
	}
	
	public function parseLeases() {
		$file = file_get_contents('/var/lib/dhcpd/dhcpd.leases');
		$devices = array();
		if(preg_match_all('/lease ([0-9\.]+) {([^}]+)}/s', $file, $matches)) {
			foreach($matches[1] as $i=>$ip) {
				$device = array();
				$meta = $matches[2][$i];
				if(preg_match('/binding state active/', $meta)) {
					$device['ip'] = $ip;
					// Parse for the lease info
	
					if(preg_match('/client-hostname "(.+)"/', $meta, $match))
						$device['client_hostname'] = $match[1];
	
					if(preg_match('/hardware ethernet ([0-9a-f:]+);/i', $meta, $match))
						$device['mac'] = $match[1];
	
					// This is the date the lease was granted, so we don't know if it was seen after this time or not
					if(preg_match('|starts \d+ (\d{4}/\d{2}/\d{2} \d{2}:\d{2}:\d{2});|i', $meta, $match))
						$device['timestamp'] = strtotime($match[1]);
						
					$devices[] = $device;
				}
			}
		}
		return $devices;
	}
		
	public function getCompanyFromMAC($mac) {
		$prefix = substr($mac, 0, 8);
		
		$query = db()->prepare('SELECT * FROM mac_companies WHERE mac_prefix = :prefix');
		$query->bindParam(':prefix', $prefix);
		$query->execute();
		
		if($company=$query->fetch())
			return $company['company'];
	
		$result = file_get_contents('http://hwaddress.com/?q=' . urlencode($mac));
		if($result) {
			if(preg_match('|<a href="/mac/[A-Z0-9-]+.html">(.+)</a>|', $result, $match)) {
				$query = db()->prepare('INSERT INTO mac_companies (mac_prefix, company) VALUES (:prefix, :company)');
				$query->bindParam(':prefix', $prefix);
				$query->bindParam(':company', $match[1]);
				$query->execute();
				return $match[1];
			}
		}
		return FALSE;
	}

	private function debug($obj='') {
		if(is_string($obj)) {
			System_Daemon::info($obj);
		} else {
			ob_start();
			print_r($obj);
			System_Daemon::info(ob_get_clean());
		}
	}
       
}
