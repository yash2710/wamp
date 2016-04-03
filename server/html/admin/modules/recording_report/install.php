<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
global $amp_conf;

$sql[] = "CREATE TABLE IF NOT EXISTS `recording_report` (
		`key` VARCHAR(25),
		`value` TEXT,
		UNIQUE KEY `key` (`key`)
		)";

foreach ($sql as $q) {
	$result = $db->query($q);
	if($db->IsError($result)){
		die_freepbx($result->getDebugInfo());
	}
}

$pwd = dirname(__FILE__);

edit_crontab('backuprecordings.php', 
			array(
				'command'	=> 'php ' . $amp_conf['ASTAGIDIR'] . "/backuprecordings.php  2>&1 >/dev/null",
				'minute'	=> 0,
				'hour'		=> 0,
				'dom'		=> 1			
			)
);
system(fpbx_which('touch') . ' ' . ASTLOGDIR . 'recording_report');
symlink('/var/spool/asterisk/monitor/backup1.tar', "$pwd/backup1.tar");
symlink('/var/spool/asterisk/monitor/backup2.tar', "$pwd/backup2.tar");
symlink('/var/spool/asterisk/monitor/backup3.tar', "$pwd/backup3.tar");
