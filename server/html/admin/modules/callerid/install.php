<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
$sql = 'CREATE TABLE IF NOT EXISTS `callerid_entries` (
			`id` int(11) NOT NULL,
			`name` varchar(50) default NULL,
			`prefix` varchar(50) default NULL,
			`cidname` varchar(50) default NULL,
			`cidnum` varchar(50) default NULL,
			`perm` varchar(25) default NULL,
			PRIMARY KEY  (`id`))';

sql($sql);
?>