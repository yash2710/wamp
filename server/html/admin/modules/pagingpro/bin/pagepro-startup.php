<?php
$freepbxconf = file_exists("/etc/freepbx.conf") ? "/etc/freepbx.conf" : "/etc/asterisk/freepbx.conf";
if(!file_exists($freepbxconf))
    die('Can not bootstrap');

$bootstrap_settings['freepbx_auth'] = false;
require_once($freepbxconf);


if (function_exists('pp_startup')) {
	pp_startup();
}
exit(0);
