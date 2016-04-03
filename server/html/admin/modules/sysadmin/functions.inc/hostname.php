<?php
	if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
	// Functions for hostname setup

	function sysadmin_get_hostname() {
		global $db;
		$ret = '';
		$sql = 'SELECT * FROM sysadmin_options WHERE `key` IN ("hostname")';
		$res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if($db->IsError($res)){
				die_freepbx($res->getDebugInfo());
		}
		foreach($res as $r) {
				$ret[$r['key']] = $r['value'];
		}

		return $ret;
	}

	function sysadmin_put_hostname($hostname) {
		global $db;
		//save to db
		//
		$data = array(
			array('hostname', trim($hostname)),
		);

		$sql = $db->prepare('REPLACE INTO sysadmin_options (`key`, `value`) VALUES (?, ?)');
		$ret = $db->executeMultiple($sql, $data);
		if($db->IsError($ret)) {
				die_freepbx($ret->getDebugInfo()."\n".$ret->getUserInfo()."\n".$db->last_query);
			}
	}

	function sysadmin_set_hostname() {
					file_put_contents('/var/spool/asterisk/sysadmin/hostname_setup', time());
	}
