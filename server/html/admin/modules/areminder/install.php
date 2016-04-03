<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
global $amp_conf, $db;

$sql = Array();

$sql[] = "CREATE TABLE IF NOT EXISTS `areminder` (
  `id` INTEGER(10) UNSIGNED NOT NULL auto_increment,
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `enabled` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `start1` INTEGER(10) SIGNED NOT NULL DEFAULT 0,
  `start2` INTEGER(10) SIGNED NOT NULL DEFAULT 0,
  `start3` INTEGER(10) SIGNED NOT NULL DEFAULT 0,
  `start4` INTEGER(10) SIGNED NOT NULL DEFAULT 0,
  `start5` INTEGER(10) SIGNED NOT NULL DEFAULT 0,
  `start6` INTEGER(10) SIGNED NOT NULL DEFAULT 0,
  `start7` INTEGER(10) SIGNED NOT NULL DEFAULT 0,
  `stop1` INTEGER(10) SIGNED NOT NULL DEFAULT 0,  
  `stop2` INTEGER(10) SIGNED NOT NULL DEFAULT 0,  
  `stop3` INTEGER(10) SIGNED NOT NULL DEFAULT 0,  
  `stop4` INTEGER(10) SIGNED NOT NULL DEFAULT 0,  
  `stop5` INTEGER(10) SIGNED NOT NULL DEFAULT 0,  
  `stop6` INTEGER(10) SIGNED NOT NULL DEFAULT 0,  
  `stop7` INTEGER(10) SIGNED NOT NULL DEFAULT 0,  
  `outcid` VARCHAR(56) NOT NULL DEFAULT '',
  `outcidname` VARCHAR(56) NOT NULL DEFAULT '',
  `cidtype` VARCHAR(24) NOT NULL DEFAULT 'default',
  `maxnotice` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
  `retrycount` SMALLINT UNSIGNED NOT NULL DEFAULT 3,
  `retrydelay` SMALLINT UNSIGNED NOT NULL DEFAULT 300,
  `greetingid` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `greeting2id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `sayname` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `saydate` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `instructionsid` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `csext` VARCHAR(64) NOT NULL DEFAULT '',
  `resched` VARCHAR(4) NOT NULL DEFAULT 'LOG',
  `schedext` VARCHAR(64) NOT NULL DEFAULT '',
  `ttsengine` INTEGER(11) NOT NULL DEFAULT 1,
  `emailcomplete` TEXT NOT NULL DEFAULT '',
  `emailfrom` TEXT NOT NULL DEFAULT '',
  `attachas` VARCHAR(4) NOT NULL DEFAULT 'CSV',
  `lastemail` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
)";

$sql[] = "CREATE TABLE IF NOT EXISTS `areminder_calls` (
  `id` INTEGER(10) UNSIGNED NOT NULL auto_increment,
  `arid` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `number` VARCHAR(24) NOT NULL DEFAULT '',
  `status` TINYINT NOT NULL DEFAULT 0,
  `retry` INTEGER NOT NULL DEFAULT 0,
  `appointment` INTEGER(10) UNSIGNED DEFAULT 0,
  `calltime` INTEGER(10) UNSIGNED DEFAULT 0,
  `length` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
)";

$sql[] = "CREATE TABLE IF NOT EXISTS `areminder_settings` (
  `id` INTEGER(10) UNSIGNED NOT NULL auto_increment,
  `key` VARCHAR(64) NOT NULL DEFAULT '',
  `value` VARCHAR(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)";

$sql[] = "CREATE TABLE IF NOT EXISTS `areminder_updates` (
  `id` INTEGER(10) UNSIGNED NOT NULL,
  `uri` VARCHAR(64) NOT NULL DEFAULT '',
  `delete` BOOLEAN DEFAULT '0',
  `update` INTEGER NOT NULL DEFAULT 300,
  `lastupdate` INTEGER(10) UNSIGNED DEFAULT 0
)";

foreach ($sql as $q) {
	$result = $db->query($q);
	if(DB::IsError($result)){
		die_freepbx($result->getDebugInfo());
	}
}


// Install Cron is done in areminder_get_config
