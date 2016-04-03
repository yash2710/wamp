<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;
global $asterisk_conf;

out('Remove all contact manager tables');
$tables = array('contactmanager_groups', 'contactmanager_group_entries', 'contactmanager_entry_numbers');
foreach ($tables as $table) {
	$sql = "DROP TABLE IF EXISTS {$table}";
	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
	unset($result);
}
