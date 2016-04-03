<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

$tables = array('virtual_queue_config', 'vqplus_queue_config', 'vqplus_qrule_config', 'vqplus_qrule_detail');
foreach ($tables as $table) {
	out(sprintf(_("dropping table %s if needed"), $table));
	sql("DROP TABLE IF EXISTS $table");
}
