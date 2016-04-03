<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
$sql[] = 'CREATE TABLE IF NOT EXISTS `conferencespro_rooms` (
  `room` varchar(25) default NULL,
  `user` varchar(50) default NULL,
  `ivr` varchar(15) default NULL,
  UNIQUE KEY `room` (`room`)
)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `conferencespro` (
  `setting` varchar(50) default NULL,
  `value` varchar(50) default NULL,
	UNIQUE KEY `setting` (`setting`)
)';

foreach ($sql as $s) {
	sql($s);
}

?>