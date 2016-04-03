#!/usr/bin/php -q
<?php

//include freepbx configuration
$restrict_mods = array('callerid' => true);
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
    include_once('/etc/asterisk/freepbx.conf');
}

$opts = getopt('u:n::m::');

if (!isset($opts, $opts['u'])) {
	//crash n burn if we dont have the settings we need
	exit(1);
}

// $matches = array();
$cid = '';
if (isset($opts['n']) && $opts['n']) {
	$cid .= '"' . $opts['n'] . '"';
// This would retain the CNAM if it weren't blank in the db and a new one wasn't set
// } else if (preg_match('/.*".*"/',$user['outboundcid'],$matches)) {
//	$cid .= $matches[0];
}
if (isset($opts['m']) && $opts['m']) {
	$cid .= ' <' . $opts['m'] . '>';
}

if (function_exists('callerid_set_cid')) {
    callerid_set_cid($opts['u'],$cid);
}
?>
