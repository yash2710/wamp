<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
global $amp_conf;

edit_crontab('import_queue_data.php', 
			array(
				'command'	=> 'php ' . $amp_conf['AMPWEBROOT'] 
						. '/admin/modules/qxact_reports/import_queue_data.php'
						. ' 2>&1 >/dev/null',
				'minute'	=> '*/5'			
			)
			);

global $db;

$sql = array();
$sql[] = "CREATE TABLE IF NOT EXISTS `qxact_calls` (
  `id` INTEGER(10) UNSIGNED NOT NULL auto_increment,
  `time` INTEGER(10) UNSIGNED DEFAULT NULL,
  `uniqueid` VARCHAR(32) NOT NULL DEFAULT '',
  `queuename` VARCHAR(32) NOT NULL DEFAULT '',
  `agent` VARCHAR(32) NOT NULL DEFAULT '',
  `event` VARCHAR(32) NOT NULL DEFAULT '',
  `callerid` VARCHAR(32) NOT NULL DEFAULT '',
  `waittime` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `calltime` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `origpos` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `position` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `did` VARCHAR(32) NOT NULL DEFAULT '',
  `key` TINYINT NOT NULL DEFAULT -1,
  PRIMARY KEY (`id`)
)";

$sql[] = "CREATE TABLE IF NOT EXISTS `qxact_agent_calls` (
  `id` INTEGER(10) UNSIGNED NOT NULL auto_increment,
  `time` INTEGER(10) UNSIGNED DEFAULT NULL,
  `uniqueid` VARCHAR(32) NOT NULL DEFAULT '',
  `queuename` VARCHAR(32) NOT NULL DEFAULT '',
  `agent` VARCHAR(32) NOT NULL DEFAULT '',
  `event` VARCHAR(32) NOT NULL DEFAULT '',
  `ringtime` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
)";

$sql[] = "CREATE TABLE IF NOT EXISTS `qxact_agent_actions` (
  `id` INTEGER(10) UNSIGNED NOT NULL auto_increment,
  `time` INTEGER(10) UNSIGNED DEFAULT NULL,
  `uniqueid` VARCHAR(32) NOT NULL DEFAULT '',
  `queuename` VARCHAR(32) NOT NULL DEFAULT '',
  `agent` VARCHAR(32) NOT NULL DEFAULT '',
  `event` VARCHAR(32) NOT NULL DEFAULT '',
  `channel` VARCHAR(32) NOT NULL DEFAULT '',
  `extension` VARCHAR(64) NOT NULL DEFAULT '',
  `logintime` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
  `reason` VARCHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)";

$sql[] = "CREATE TABLE IF NOT EXISTS `qxact_system_events` (
  `id` INTEGER(10) UNSIGNED NOT NULL auto_increment,
  `time` INTEGER(10) UNSIGNED DEFAULT NULL,
  `queuename` VARCHAR(32) NOT NULL DEFAULT '',
  `event` VARCHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)";

$sql[] = "CREATE TABLE IF NOT EXISTS `qxact_reports` (
  `id` INTEGER(10) UNSIGNED NOT NULL auto_increment,
  `name` VARCHAR(256) NOT NULL DEFAULT 'UNNAMED REPORT',
  `data` TEXT,
  PRIMARY KEY (`id`)
)";


foreach ($sql as $q) {
	$result = $db->query($q);
	if(DB::IsError($result)){
		die_freepbx($result->getDebugInfo());
	}
}



// these sql queries are not as important since if they fail it means that the
// indexes already exist

$sql = array();
$sql[] = "CREATE INDEX uniqueid_index ON qxact_calls (`uniqueid`)";
$sql[] = "CREATE INDEX queuename_index ON qxact_calls (`queuename`)";
$sql[] = "CREATE INDEX agent_index ON qxact_calls (`agent`)";
$sql[] = "CREATE INDEX event_index ON qxact_calls (`event`)";
$sql[] = "CREATE INDEX waittime_index ON qxact_calls (`waittime`)";
$sql[] = "CREATE INDEX calltime_index ON qxact_calls (`calltime`)";
$sql[] = "CREATE INDEX time_index ON qxact_calls (`time`)";


foreach ($sql as $q) {
	$result = $db->query($q);
	// ignore errors as keys may already exist 
}


// adding DID column if it does not exist

$sql = "SELECT did FROM qxact_calls LIMIT 1";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if($db->IsError($check)) {
    // add new field
    $sql = "ALTER TABLE qxact_calls ADD did VARCHAR(32) NOT NULL DEFAULT ''";
    $result = $db->query($sql);
    if($db->IsError($result)) { 
		die_freepbx($result->getDebugInfo()); 
	}
}

