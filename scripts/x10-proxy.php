#!/usr/bin/php
<?php
/**
 * This script listens for incoming UDP packets of the format:
 *  !on A1
 *  !off A1
 * and passes them off to the heyu daemon for controlling X10 lights in the house.
 */

// http://kevin.vanzonneveld.net/techblog/article/create_daemons_in_php/
// Requires PEAR package System_Daemon
require_once "System/Daemon.php";
$options = array(
    'appName' => 'x10_proxy',
    'appDir' => dirname(__FILE__),
    'appDescription' => 'Passes messages between UDP and an X10 controller',
    'authorName' => 'Aaron Parecki',
    'authorEmail' => 'aaron@parecki.com',
    'sysMaxExecutionTime' => '0',
    'sysMaxInputTime' => '0',
    'sysMemoryLimit' => '1024M',
    'appRunAsGID' => 500,
    'appRunAsUID' => 500,
); 
System_Daemon::setOptions($options);
System_Daemon::start();

// Only enable this the first time to write the init.d script
# $path = System_Daemon::writeAutoRun();


set_include_path(dirname(__FILE__).'/../include/:' . get_include_path());
include('inc.php');


$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP) or die("Could not create socket\n");

$listen = X10_PROXY_IP;
$port = X10_PROXY_PORT;

$bind = socket_bind($socket, $listen, $port) or die("Could not bind to socket\n");
$i = 1;

$input = '';

while(TRUE)
{
	$bytes = socket_recvfrom($socket, $input, 1024, 0, $listen, $port);
	
	if (trim($input) != "")
	{
		$command = unserialize($input);	
		if(preg_match('/!(on|off) ([^ ]+)/', $command['command'], $match))
		{
			shell_exec('/usr/local/bin/heyu ' . $match[1] . ' ' . $match[2]);
		}
	}
	
	$i++;
}

socket_close($socket);
