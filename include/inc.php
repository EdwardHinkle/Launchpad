<?php

define('PDO_DSN', 'mysql:dbname=launchpad;host=127.0.0.1');
define('PDO_USER', 'system');
define('PDO_PASS', '');

$db = new PDO(PDO_DSN, PDO_USER, PDO_PASS);



