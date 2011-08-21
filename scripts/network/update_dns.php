#!/usr/bin/php
<?php
set_include_path(dirname(__FILE__).'/../../include/:' . get_include_path());
include('inc.php');

/* starting from the template files here, create the conf and zone files for the dns server after fetching the dhcp data from 10.0.0.1 */

// get the hash of the existing file
if( file_exists('tmp/dhcpd.conf') ) {
	$hash_old = md5(file_get_contents('tmp/dhcpd.conf'));
} else {
	$hash_old = "";
}

// get the hash of the new file
$dhcp_data = file_get_contents('/etc/dhcp/dhcpd.conf');
$hash_new = md5($dhcp_data);

if( $hash_new != $hash_old || @$argv[1] == '-f'  ) {

	$dns_rdo = file_get_contents('dns.template');
	$dns_reverse = file_get_contents('reverse.template');

	                         # a negative lookahead assertion!
	preg_match_all('/host ((?!fix).+) .+\s+[^}]*fixed-address ([0-9\.]+);[^}]*}/',$dhcp_data,$hosts);
	for( $i=0; $i<count($hosts[1]); $i++ ) {
	        $hostname = $hosts[1][$i];
	        $ip = $hosts[2][$i];
	
	        echo $ip.' = '.$hostname."\n";
		$ipr = implode('.',array_slice(array_reverse(explode('.',$ip)),0,3));
		$dns_reverse .= $ipr."\t\tIN\tPTR\t".$hostname.".pad.\n";
	
		$dns_rdo .= $hostname.".pad.\t7200\tIN\tA\t".$ip."\n";
		$dns_rdo .= "*.".$hostname.".pad.\t7200\tIN\tA\t".$ip."\n";
	
		echo "\n";
	}

	echo "Updating dns with new zones...\n";

	echo $dns_reverse."\n";
	echo $dns_rdo."\n";

	$fp = fopen('/var/named/zone/10.10.db','w');
	fwrite($fp, $dns_reverse);
	fclose($fp);

	$fp = fopen('/var/named/zone/pad.db','w');
	fwrite($fp, $dns_rdo);
	fclose($fp);

	// restart dns server
	echo shell_exec('service named restart');
}
