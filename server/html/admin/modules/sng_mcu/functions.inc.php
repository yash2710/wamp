<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

$dir = dirname(__FILE__);
require_once($dir . '/functions.inc/api.php');
require_once($dir . '/functions.inc/getter_setter.php');
require_once($dir . '/functions.inc/dialplan.php');
require_once($dir . '/functions.inc/destinations.php');

function clean_post($needle, $haystack) {
	$var = array();
	foreach ($needle as $k => $v) {
	    $var[$k] = isset($haystack[$k]) ? $haystack[$k] : $v;
	}
	return $var;
}
