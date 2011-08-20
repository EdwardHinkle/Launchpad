#!/usr/bin/php
<?php
set_include_path(dirname(__FILE__).'/../include/:' . get_include_path());
include('inc.php');

`/bin/stty -F /dev/ttyS0 2400`;

$fp = fopen ("/dev/ttyS0", "w+");

if (!$fp) {
	echo "Uh-oh. Port not opened.";
} else {

	$temp = array();

	$server_msg = fgets($fp);
	echo $server_msg;
	for( $i=1; $i<=8; $i++ ) {
		$line = fgets($fp);
		if( trim($line) != "" ) {
			$data = explode(' ',$line);
			$temp[trim($data[0])] = $data[1];
		}
	}

	fclose($fp);

	foreach( $temp as $s=>$t ) {
		$f = ($t * 9 / 5) + 32;
		switch($s) {
			case 1:
				$s = 'Closet';
				break;
			case 2:
				$s = 'Inside';
				break;
			case 3:
				$s = 'Outside';
				break;
		}
		$SQL = "INSERT INTO temperature (date, sensor, temperature) VALUES (NOW(), '$s', $f)";

		$query = $db->prepare('INSERT INTO temperature (date, sensor, temperature) VALUES (NOW(), :s, :f)');
		$query->bindParam(':f', $f);
		$query->bindParam(':s', $s);
		$query->execute();

		$query = $db->prepare('UPDATE last_temperature SET date=NOW(), temperature=:f WHERE sensor=:s');
		$query->bindParam(':f', $f);
		$query->bindParam(':s', $s);
		$query->execute();

		echo $f."\n";
	}

}


?>
