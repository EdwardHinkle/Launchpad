<?php
class LaunchBot
{
	private $sock;
	private $port;
	private $portmap;
	
	function __construct()
	{
		$this->portmap = $GLOBALS['IRC_CHANNELS'];
	
		$port = trim($port, '#');

		if(!array_key_exists($port, $this->portmap))
			return FALSE;
		
		$this->port = $this->portmap[$port];
		$this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
	}
	
	function Send($msg, $port="")
	{
		if($port == "")
			$port = $this->port;
		else
			$port = $this->portmap[trim(strtolower($port), '#')];

		socket_sendto($this->sock, $msg, strlen($msg), 0, IRC_BOT_HOSTNAME, $port);
	}
}
