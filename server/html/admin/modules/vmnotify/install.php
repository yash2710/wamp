<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
global $amp_conf, $db;

$sql = Array();

$sql[] = "CREATE TABLE IF NOT EXISTS `vmnotify` (
  `mailbox` VARCHAR(20) NOT NULL,
  `recipients` TEXT NOT NULL DEFAULT '',
  `outcid` VARCHAR(56) NOT NULL DEFAULT '',
  `outcidname` VARCHAR(56) NOT NULL DEFAULT '',
  `cidtype` VARCHAR(24) NOT NULL DEFAULT 'default',
  `getname` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `retrycount` SMALLINT UNSIGNED NOT NULL DEFAULT 3,
  `retrydelay` SMALLINT UNSIGNED NOT NULL DEFAULT 300,
  `prioritydelay` SMALLINT UNSIGNED NOT NULL DEFAULT 120,
  `emailsuccess` TEXT NOT NULL DEFAULT '',
  `emailfail` TEXT NOT NULL DEFAULT '',
  `emailfrom` VARCHAR(256) NOT NULL DEFAULT '',
  `emailsubject` VARCHAR(256) NOT NULL DEFAULT '',
  `emailbody` TEXT NOT NULL DEFAULT '',
  `emailattach` TINYINT NOT NULL DEFAULT 1,
  `enabled` TINYINT NOT NULL DEFAULT 1,
  `greetingid` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
  `instructionsid` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`mailbox`)
)";



$sql[] = "CREATE TABLE IF NOT EXISTS `vmnotify_notifications` (
  `id` INTEGER(10) UNSIGNED NOT NULL auto_increment,
  `mailbox` VARCHAR(20) NOT NULL DEFAULT '',
  `status` TINYINT NOT NULL DEFAULT 0,
  `retry` INTEGER NOT NULL DEFAULT 0,
  `priority` INTEGER NOT NULL DEFAULT 0,
  `time` INTEGER(10) UNSIGNED DEFAULT NULL,
  `callerid` VARCHAR(64) NOT NULL DEFAULT '',
  `length` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
)";

$sql[] = "CREATE TABLE IF NOT EXISTS `vmnotify_events` (
  `id` INTEGER(10) UNSIGNED NOT NULL auto_increment,
  `notification_id` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
  `time` INTEGER(10) UNSIGNED DEFAULT NULL,
  `number` VARCHAR(32) NOT NULL DEFAULT '',
  `priority` INTEGER(10) NOT NULL DEFAULT 0,
  `status` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
)";

foreach ($sql as $q) {
	$result = $db->query($q);
	if(DB::IsError($result)){
		die_freepbx($result->getDebugInfo());
	}
}

// adding recordings columns if they don't exist

$sql = "SELECT greetingid, instructionsid FROM vmnotify LIMIT 1";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if($db->IsError($check)) {
    // add new fields
    $xsql[] = "ALTER TABLE vmnotify ADD `greetingid` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0";
    $xsql[] = "ALTER TABLE vmnotify ADD `instructionsid` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0";
	foreach($xsql as $q) {
	    $result = $db->query($q);
	    if($db->IsError($result)) { 
			die_freepbx($result->getDebugInfo()); 
		}
	}
}

// check to make sure vm_general.inc has externnotify set

$cmd = "[ `grep externnotify /etc/asterisk/voicemail.conf | grep -v \# | wc -l` -eq 0 ] && sed -i 's/\[general\]/\[general\]\\nexternnotify=\/var\/lib\/asterisk\/agi-bin\/vmnotify-newvm.php/g' /etc/asterisk/voicemail.conf";
exec($cmd);
