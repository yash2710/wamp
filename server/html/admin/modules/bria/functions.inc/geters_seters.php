<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function bria_get_allSettings() {
	$sql = "SELECT * FROM bria_settings";

	$results = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);

	return $results;
}

function bria_get_setting($setting) {
	global $db;

	$setting = $db->escapeSimple($setting);

	$sql = "SELECT value FROM bria_settings WHERE `key` = '".$setting."'";

	$results = sql($sql, 'getRow', DB_FETCHMODE_ASSOC);

	$results = (!empty($results['value'])) ? $results['value'] : null;

	return $results;
}

function bria_set_setting($setting, $value) {
	global $db;

	$setting = $db->escapeSimple($setting);
	$value = $db->escapeSimple($value);

	$sql = "REPLACE INTO bria_settings (`key`, `value`) VALUES('".$setting."','".$value."')";

	$results = sql($sql);

	return $results;
}

function bria_set_multiple_settings($settings = array()) {
	global $db;

	if (!empty($settings)) {

		foreach ($settings as $key => $value) {
			$s[] = array($key, $value);
		}

		$sth = $db->prepare('REPLACE INTO bria_settings(`key`, `value`) VALUES (?,?)');
		$results = $db->executeMultiple($sth, $s);

		if (!$db->isError($results)) {
			return true;
		}
	}

	return false;
}

function bria_get_prefix() {
	$prefix = bria_get_setting('device_prefix');

	$prefix = !empty($prefix) ? $prefix : '999';

	return $prefix;
}

function bria_get_users_enabled() {
	$sql = "SELECT u.id, u.username, u.default_extension, u.fname, u.lname
		FROM freepbx_users u
		JOIN freepbx_users_settings s ON s.uid = u.id
		WHERE s.module = 'bria'
		AND s.key = 'enabled'
		AND s.val = 1";

	$results = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);

	return $results;
}
