#!/usr/bin/env php
<?php
$shortopts  = "";
$shortopts .= "p:t:";  // Required value

$options = getopt($shortopts);
if(!empty($options['p']) && !empty($options['t']) && is_numeric($options['p']) && is_numeric($options['t'])) {
    $freepbxconf = file_exists("/etc/freepbx.conf") ? "/etc/freepbx.conf" : "/etc/asterisk/freepbx.conf";
    if(!file_exists($freepbxconf))
        die('Can not bootstrap');

    $bootstrap_settings['freepbx_auth'] = false;
    require_once($freepbxconf);
    
    pp_execute_pager($options);

    exit(0);
} else {
    //edit_crontab('pagepro-scheduler.php');
    exit(1);
}