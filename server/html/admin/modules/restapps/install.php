<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
global $db;

$modversions = array(
	"announcement" => "2.11.0.1",
	"callback" => "2.11.0.1",
	"callrecording" => "2.11.0.5",
	"cdr" => "2.11.0.6",
	"core" => "2.11.0.19",
	"dahdiconfig" => "2.11.40",
	"endpoint" => "2.11.0.1.46",
	"framework" => "2.11.0.31",
	"hotelwakeup" => "2.11.2",
	"languages" => "2.11.0.1",
	"miscdests" => "2.11.0.1",
	"parking" => "2.11.0.14",
	"parkpro" => "2.11.0.5",
	"queueprio" => "2.11.0.1",
	"superfecta" => "2.11.11",
	"timeconditions" => "2.11.0.4"
);

$mods = modulelist::create($db);

$version_check = true;
foreach ($modversions as $module => $version) {
	if (isset($mods->module_array[$module])) {
		if (!version_compare($mods->module_array[$module]['version'], $version, "ge")) {
			out($mods->module_array[$module]['name'] . " must be upgraded to version " . $version . " or higher.");
			$version_check = false;
		}
	}
}
if (!$version_check) {
	return false;
}

$sql[] = 'CREATE TABLE IF NOT EXISTS `restapps_settings` (
 `module` varchar(25),
 `key` varchar(25),
 `value` varchar(80) default NULL,
 PRIMARY KEY (`module`, `key`)
);';

$sql[] = 'CREATE TABLE IF NOT EXISTS `restapps_stats` (
 `brand` varchar(80) NOT NULL,
 `model` varchar(80) NOT NULL,
 `application` varchar(80) NOT NULL,
 `page` varchar(80) NOT NULL,
 `timestamp` int(11)
);';

foreach ($sql as $statement){
	$check = $db->query($statement);
	if (DB::IsError($check)){
		die_freepbx( "Can not execute $statement : " . $check->getMessage() .  "\n");
	}
}

outn(_("checking for timestamp field.."));
$sql = "SELECT `timestamp` FROM restapps_stats";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
	$sql = "ALTER TABLE restapps_stats ADD `timestamp` int(11)";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		out(_("ERROR failed to update timestamp field"));
	} else {
		out(_("OK"));
	}
} else {
	out(_("already exists"));
}

mkdir($amp_conf['AMPWEBROOT'] . '/restapps');
symlink(dirname(__FILE__) . '/restapps.php', $amp_conf['AMPWEBROOT'] . '/restapps/restapps.php');
symlink(dirname(__FILE__) . '/applications.php', $amp_conf['AMPWEBROOT'] . '/restapps/applications.php');
symlink(dirname(__FILE__) . '/sync.php', $amp_conf['AMPWEBROOT'] . '/restapps/sync.php');

file_put_contents('/var/spool/asterisk/sysadmin/restapps_restart', time());

?>
