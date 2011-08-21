<?php

define('PDO_DSN', 'mysql:dbname=launchpad;host=127.0.0.1');
define('PDO_USER', '');
define('PDO_PASS', '');

define('X10_PROXY_IP', '0.0.0.0');
define('X10_PROXY_PORT', 1234);

// IRC Bot Config
// Set up an array of channels mapping to ports
// Assumes a MediaWiki RecentChanges bot is listening on the other side
$IRC_CHANNELS = array(
	'aaronpk' => 51000,
	// etc
);
define('IRC_BOT_HOSTNAME', 'localhost');
define('IRC_DEFAULT_CHANNEL', 'launchpad');
