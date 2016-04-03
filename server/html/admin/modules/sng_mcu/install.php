<?php if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db, $amp_conf;

$sql[] = "CREATE TABLE IF NOT EXISTS `sng_mcu_details` (
		`id` int(11) NOT NULL auto_increment,
		`host` varchar(150) NOT NULL,
		`auth` tinyint(1) default 0,
		`token` text default NULL,
		PRIMARY KEY (`id`))";

$sql[] = "CREATE TABLE IF NOT EXISTS `sng_mcu_entries` (
		`id` int(11) NOT NULL auto_increment,
		`sng_mcu_id` int(11) NOT NULL,
		`ext` varchar(50) default NULL,
		`conf` varchar(50) NOT NULL,
		`name` varchar(50) default NULL,
		`announcement` tinyint(11) default NULL,
		PRIMARY KEY (`id`))";

foreach ($sql as $q) {
	$result = $db->query($q);
	if($db->IsError($result)){
		die_freepbx($result->getDebugInfo());
	}
}

outn(_("checking for sync field.."));
$sql = "SELECT `sync` FROM sng_mcu_details";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
	$sql =  "ALTER TABLE sng_mcu_details ADD `sync` VARCHAR(20) NULL ;";
	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
	out(_("OK"));
} else {
	out(_("Already exists"));
}
