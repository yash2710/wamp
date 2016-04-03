<?php
//include this in a page with:
//bootstrap
$bootstrap_settings['freepbx_auth'] = false;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
	  include_once('/etc/asterisk/freepbx.conf');
}

$get_vars = array('i', 'p');
foreach ($get_vars as $g) {
	$var[$g] = isset($_REQUEST[$g]) ? $_REQUEST[$g] : '';
}

//if there has been no phone number posted
if (!$var['p']) {
    if (file_exists('/etc/schmooze/wcb.html')) {
        echo file_get_contents('/etc/schmooze/wcb.html');
    } else {
        echo webcallback_iframe($var['i']);
    }
} else {
	webcallback_call($var['p'], $var['i']);
}

?>
