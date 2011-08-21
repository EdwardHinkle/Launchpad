<?php
include(dirname(__FILE__).'/config.php');
include('LaunchBot.php');

function bot($channel=IRC_DEFAULT_CHANNEL) {
	static $bot;
	if(!isset($bot))
	{
		$bot = new LaunchBot($channel);
	}
	return $bot;
}


function db() {
	static $db;
	if(!isset($db))
	{
		try {
			$db = new PDO(PDO_DSN, PDO_USER, PDO_PASS);
		} catch (PDOException $e) {
			die('Connection failed: ' . $e->getMessage());
		}
	}
	return $db;
}

function k($obj, $key, $default=NULL) {
	if(is_array($obj))
		return array_key_exists($key, $obj) ? $obj[$key] : $default;
	elseif(is_object($obj))
		return property_exists($obj, $key) ? $obj->{$key} : $default;
	else
		return $default;
}
