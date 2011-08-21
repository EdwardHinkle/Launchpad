#!/usr/bin/php
<?php
set_include_path(dirname(__FILE__).'/../include/:' . get_include_path());
include('inc.php');

$mode = array_key_exists(1, $argv) ? $argv[1] : '';

$last = db()->query('SELECT * FROM last_temperature');
$sensors = array();
while($sensor = $last->fetch(PDO::FETCH_OBJ))
        $sensors[$sensor->sensor] = array('date'=>$sensor->date, 'temperature'=>$sensor->temperature);

if($mode == 'config')
{
    echo 'graph_title Temperature' . "\n";
    echo 'graph_info Shows the temperature from the sensors around the house.' . "\n";
    echo 'graph_vlabel fahrenheit' . "\n";
    echo 'graph_category environment' . "\n";
    // echo 'graph_args --lower-limit 0' . "\n";
    echo 'graph_scale yes' . "\n";

	foreach($sensors as $k=>$v) {
		echo $k . '.label ' . $k . "\n";
	}
}
else
{
	foreach($sensors as $k=>$v) {
		echo $k . '.value ' . $v['temperature'] . "\n";
	}
}


