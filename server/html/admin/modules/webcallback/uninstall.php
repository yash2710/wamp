<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
sql('DROP TABLE IF EXISTS webcallback');

$target = $amp_conf['AMPWEBROOT'] . '/wcb.php';
if (file_exists($target)) {
	unlink($target);
}
?>