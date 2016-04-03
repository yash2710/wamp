<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

//for translation only
if (false) {
_("Park Prefix");
}

$fcc = new featurecode('parkpro', 'park');
$fcc->setDescription('Park Prefix');
$fcc->setDefault('*86');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);


$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql['parkplus_device'] = "
	CREATE TABLE IF NOT EXISTS parkplus_device (
		device_id VARCHAR (20) NOT NULL,
		parkplus_id INTEGER NOT NULL DEFAULT 1,
		PRIMARY KEY (device_id)
	)";

$sql['parkplus_announce'] = "
	CREATE TABLE IF NOT EXISTS parkplus_announce (
		id INTEGER NOT NULL $autoincrement,
		announceext VARCHAR(40) NOT NULL DEFAULT '',
		name VARCHAR(40) NOT NULL DEFAULT '',
		record_message VARCHAR(10) NOT NULL DEFAULT 'no',
		record_announcement_id varchar(45) DEFAULT NULL,
		record_message_length int(11) DEFAULT '5',
		record_message_silence int(11) DEFAULT '3',
		successful_recording_id varchar(45) DEFAULT NULL,
		failure_recording_id varchar(45) DEFAULT NULL,
		park_id INTEGER NOT NULL,
		primary_announcement_id varchar(45) DEFAULT NULL,
		replace_announcement_id varchar(45) DEFAULT NULL,
		parkingtime INTEGER NOT NULL DEFAULT 45,
		parkingtime_enable tinyint(1) DEFAULT 0,
		parkingretry INTEGER NOT NULL DEFAULT 1,
		comebacktoorigin VARCHAR(10) NOT NULL DEFAULT 'yes',
		dest VARCHAR(100) NOT NULL DEFAULT '',		  
		page_id INTEGER NOT NULL,
		slot_announce_enable VARCHAR(10) NOT NULL DEFAULT 'yes',
		page_announcement_id_1 varchar(45) DEFAULT NULL,
		page_announcement_id_2 varchar(45) DEFAULT NULL,
		page_announcement_id_3 varchar(45) DEFAULT NULL,
		cidpp VARCHAR(100) NOT NULL DEFAULT '',
		autocidpp VARCHAR(10) NOT NULL DEFAULT 'none',
		page_timer_extend INTEGER NOT NULL DEFAULT '0',
		announce_order BLOB,
		PRIMARY KEY (id)
		)";

foreach ($sql as $t => $s) {
	out(sprintf(_("creating table %s if needed"), $t));
	$result = $db->query($s);
	if(DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
}

if (!$db->getAll('SHOW COLUMNS FROM parkplus_announce WHERE FIELD = "page_announcement_id"')) {
	sql('ALTER TABLE parkplus_announce ADD page_announcement_id varchar(45) DEFAULT NULL');
}

if (!$db->getAll('SHOW COLUMNS FROM parkplus_announce WHERE FIELD = "parkingtime_enable"')) {
	sql('ALTER TABLE parkplus_announce ADD parkingtime_enable tinyint(1) DEFAULT 0 AFTER parkingtime');
}

if (!$db->getAll('SHOW COLUMNS FROM parkplus_announce WHERE FIELD = "page_announcement_id_1"')) {
	sql('ALTER TABLE parkplus_announce CHANGE page_announcement_id page_announcement_id_1 varchar(45) DEFAULT NULL');
}

if (!$db->getAll('SHOW COLUMNS FROM parkplus_announce WHERE FIELD = "page_announcement_id_2"')) {
	out(_("Adding additional paging announcement fields"));
	sql('ALTER TABLE parkplus_announce ADD page_announcement_id_2 varchar(45) DEFAULT NULL AFTER page_announcement_id_1');
}

if (!$db->getAll('SHOW COLUMNS FROM parkplus_announce WHERE FIELD = "page_announcement_id_3"')) {
	sql('ALTER TABLE parkplus_announce ADD page_announcement_id_3 varchar(45) DEFAULT NULL AFTER page_announcement_id_2');
	
	out(_("Restructuring Paging Announcement Order"));
	$sql = "SELECT id, announce_order FROM parkplus_announce";
	$res = sql($sql,'getAll',DB_FETCHMODE_ASSOC);
	foreach($res as $all) {
		$order = json_decode($all['announce_order'],TRUE);
		$k = array_search('pa_pa',$order);
		if($k !== FALSE) {
			$order[$k] = "pa_pa1";
			$order[] = "pa_pa2";
			$order[] = "pa_pa3";
			$json = json_encode($order);
			$sql = "UPDATE parkplus_announce SET announce_order = '".$db->escapeSimple($json)."' WHERE id = ".$all['id'];
			sql($sql);
		}
	}
}

if (!$db->getAll('SHOW COLUMNS FROM parkplus_announce WHERE FIELD = "record_announcement_id"')) {
	out(_("Adding Record Message Announcement Field"));
	sql('ALTER TABLE parkplus_announce ADD record_announcement_id varchar(45) DEFAULT NULL AFTER record_message');
}

if (!$db->getAll('SHOW COLUMNS FROM parkplus_announce WHERE FIELD = "slot_announce_enable"')) {
	out(_("Adding Slot Announce Enable Field"));
	sql("ALTER TABLE parkplus_announce ADD slot_announce_enable VARCHAR(10) NOT NULL DEFAULT 'yes' AFTER page_id");
}

if (!$db->getAll('SHOW COLUMNS FROM parkplus_announce WHERE FIELD = "record_message_silence"')) {
	out(_("Adding Silence Successful/failure messages"));
	sql('ALTER TABLE parkplus_announce ADD record_message_silence int(11) DEFAULT "3" AFTER record_message_length');
	sql('ALTER TABLE parkplus_announce ADD successful_recording_id varchar(45) DEFAULT NULL AFTER record_message_silence');
	sql('ALTER TABLE parkplus_announce ADD failure_recording_id varchar(45) DEFAULT NULL AFTER successful_recording_id');
}