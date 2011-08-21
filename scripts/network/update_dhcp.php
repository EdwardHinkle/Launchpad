#!/usr/bin/php
<?php
set_include_path(dirname(__FILE__).'/../../include/:' . get_include_path());
include('inc.php');

if(trim(shell_exec('whoami')) != 'root') {
	echo "Must be run as root\n";
	die();
}

$query = db()->query('SELECT * FROM dns WHERE ip != "" ORDER BY ip, hostname');

$dhcpdconf = file_get_contents('dhcp.template');

$hostnamePrefix = 'pad-';

while($record=$query->fetch(PDO::FETCH_ASSOC))
{
	if( $record['hostname'] )
		$hostname = $record['hostname'];
	else
		if( $record['ip'] )
			$hostname = $hostnamePrefix . implode('-', array_slice(explode('.', $record['ip']), 2, 2));
		else
			$hostname = $hostnamePrefix . $record['id'];

	$dhcpdconf .= 'host ' . $hostname . " {\n";
	$dhcpdconf .= "\thardware ethernet " . $record['mac'] . ";\n";
	if( $record['ip'] ) $dhcpdconf .= "\tfixed-address " . $record['ip'] . ";\n";
	$dhcpdconf .= "\toption host-name \"$hostname\";\n";
	if( $record['router_ip'] ) $dhcpdconf .= "\toption routers " . $record['router_ip'] . ";\n";
	$dhcpdconf .= "}\n\n";
}

echo $dhcpdconf;

file_put_contents('/etc/dhcp/dhcpd.conf', $dhcpdconf);

echo shell_exec('systemctl restart dhcpd.service');
