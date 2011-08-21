#!/usr/bin/php
<?php
set_include_path(dirname(__FILE__).'/../include/:' . get_include_path());
include('inc.php');

$ch = curl_init(DYNDNS_PATH);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, DYNDNS_USERNAME . ':' . DYNDNS_PASSWORD);
curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__).'/../include/AaronPareckiCA.crt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
	'hostname' => DYNDNS_HOST
)));
$response = curl_exec($ch);

echo $response;
echo "\n";
