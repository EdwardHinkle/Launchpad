#!/usr/bin/php
<?php
set_include_path(dirname(__FILE__).'/../../include/:' . get_include_path());
include('inc.php');
include('DHCPPresence.php');

// Allowed arguments & their defaults
$runmode = array(
    'no-daemon' => false,
    'help' => false,
    'write-initd' => false,
);
 
// Scan command line attributes for allowed arguments
foreach ($argv as $k=>$arg) {
    if (substr($arg, 0, 2) == '--' && isset($runmode[substr($arg, 2)])) {
        $runmode[substr($arg, 2)] = true;
    }
}
 
// Help mode. Shows allowed argumentents and quit directly
if ($runmode['help'] == true) {
    echo 'Usage: '.$argv[0].' [runmode]' . "\n";
    echo 'Available runmodes:' . "\n";
    foreach ($runmode as $runmod=>$val) {
        echo ' --'.$runmod . "\n";
    }
    die();
}

require_once 'System/Daemon.php';
 
// Setup
$options = array(
    'appName' => 'dhcp_presence',
    'appDir' => dirname(__FILE__),
    'appDescription' => 'Watches the DHCP logs and tries to figure out who is currently present',
    'authorName' => 'Aaron Parecki',
    'authorEmail' => 'aaron@parecki.com',
    'sysMaxExecutionTime' => '0',
    'sysMaxInputTime' => '0',
    'sysMemoryLimit' => '512M',
#    'appRunAsGID' => 500,
#    'appRunAsUID' => 500,
);
 
System_Daemon::setOptions($options);
 
// This program can also be run in the forground with runmode --no-daemon
if (!$runmode['no-daemon']) {
    // Spawn Daemon
    System_Daemon::start();
}
 
// With the runmode --write-initd, this program can automatically write a
// system startup file called: 'init.d'
// This will make sure your daemon will be started on reboot
if (!$runmode['write-initd']) {
    System_Daemon::info('not writing an init.d script this time');
} else {
    if (($initd_location = System_Daemon::writeAutoRun()) === false) {
        System_Daemon::notice('unable to write init.d script');
    } else {
        System_Daemon::info(
            'sucessfully written startup script: %s',
            $initd_location
        );
    }
}


$presence = new DHCPPresence();
$presence->startWatchingLog();

