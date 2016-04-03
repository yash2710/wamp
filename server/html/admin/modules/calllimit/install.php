<?php
// vim: set filetype=php tabstop=4 shiftwidth=4 autoindent smartindent:

if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }


global $db;
global $amp_conf;

// Does the config database already exist?
$q = "SELECT * FROM `calllimit`";
$check = $db->query($q);
if (DB::IsError($check)) {
    // No. Create the new one.
	calllimit_createdb();
} else {
    // It exists. Is it current?
    $q = "SELECT `key` from `calllimit`";
    $check = $db->query($q);
    if(DB::IsError($check)) {
        // It's not. Upgrade it. 
        calllimit_upgrade();
    }
}

function calllimit_upgrade() {
	global $db;
	// Upgrade old config database to new key/value store.

	$q = "SELECT * FROM `calllimit`";
	$res = sql($q, "getAll", DB_FETCHMODE_ASSOC);

	// Drop the table.
	sql("DROP TABLE `calllimit`");
	// Re-create it.
	calllimit_createdb();

	// Add everything back in.
	$p = $db->prepare('INSERT INTO `calllimit` VALUES (?, ?)');
	foreach ($res as $result) {
		$id=$result['id'];
		$db->execute($p, array($id."_name", $result['name']));
		$db->execute($p, array($id."_periodtype_1", "Days"));
		$db->execute($p, array($id."_qty_1", $result['call_limit']));
		if ($result['date_range'] == 0)
			$result['date_range'] = 1;
		$db->execute($p, array($id."_periodlength_1", $result['date_range']));
		$db->execute($p, array($id."_recording", $result['recording']));
		$db->execute($p, array($id."_recordingid", recordings_get_id($result['recording'])));
		$db->execute($p, array($id."_id", $id)); // For MAX() queries. 
	}
	exit;
}

function calllimit_createdb() {
	global $db;
	$q = "CREATE TABLE `calllimit` (
	`key` CHAR(64) NOT NULL, 
	`value` VARCHAR(64), 
	PRIMARY KEY (`key`))";
	$check = $db->query($q);
	if(DB::IsError($check)) {
        	die_freepbx("Can not create calllimit tables\n".$check->getDebugInfo());
    	}
	$q = "CREATE TABLE IF NOT EXISTS calllimit_usage (
	`calllimit_id` INT NOT NULL,
	`dispname` VARCHAR( 30 ),
	`foreign_id` VARCHAR( 30 ),
	PRIMARY KEY (`dispname`, `foreign_id`))";
	$check = $db->query($q);
	if (DB::IsError($check)) {
		die_freepbx("Can not create calllimit usage tables\n".$check->getDebugInfo());
	}
}
