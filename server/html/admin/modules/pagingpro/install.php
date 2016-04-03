<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
sql('CREATE TABLE IF NOT EXISTS `pagingpro` (
 	`page_id` varchar(10) NOT NULL default "",
  	`busypage` varchar(50) default NULL,
	`intro_recording` varchar(50) default NULL,
	`cid_prepend` varchar(50) default NULL,
    `enable_scheduler` TINYINT(1) default 0,
  PRIMARY KEY  (`page_id`)
)');

sql('CREATE TABLE IF NOT EXISTS `pagingpro_core_routing` (
	`route` varchar(25) NOT NULL default "",
	`page_id` varchar(25) default NULL,
	PRIMARY KEY  (`route`)
)');

//add cid_repend field if its missing
if (!$db->getAll('SHOW COLUMNS FROM pagingpro WHERE FIELD = "cid_prepend"')) {
	sql('ALTER TABLE pagingpro ADD cid_prepend varchar(50)');
}

//remove column that was added accidently
if ($db->getAll('SHOW COLUMNS FROM pagingpro WHERE FIELD = "cid_perpend"')) {
	sql('ALTER TABLE pagingpro DROP COLUMN cid_perpend');
}

//add scheduler field if it was missing
if (!$db->getAll('SHOW COLUMNS FROM pagingpro WHERE FIELD = "enable_scheduler"')) {
	sql('ALTER TABLE pagingpro ADD `enable_scheduler` TINYINT(1) default 0');
}

// PAGINGMAXPARTICIPANTS
$freepbx_conf =& freepbx_conf::create();
$set['value'] = '40';
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = 'pagingpro';
$set['category'] = 'Paging';
$set['emptyok'] = 0;
$set['name'] = 'Max Paging Participants';
$set['description'] = 'Maximum amount of particpants allowed in a page';
$set['type'] = CONF_TYPE_INT;
$freepbx_conf->define_conf_setting('PAGINGMAXPARTICIPANTS',$set, true);

sql('CREATE TABLE IF NOT EXISTS `pagingpro_scheduler_events` (
    `event_num` varchar(4) NOT NULL default "",
 	`page_id` varchar(10) NOT NULL default "",
    `times` BLOB
)');
    
sql('CREATE TABLE IF NOT EXISTS `pagingpro_scheduler_range` (
 	`page_id` varchar(10) NOT NULL default "",
    `starttime` int(11),
    `endtime` int(11)
)');
    
sql('CREATE TABLE IF NOT EXISTS `pagingpro_scheduler_exclusions` (
 	`page_id` varchar(10) NOT NULL default "",
    `time` int(11),
    `comment` varchar(250) NOT NULL default ""
)');
    
sql('CREATE TABLE IF NOT EXISTS `pagingpro_scheduler_crons` (
 	`page_id` varchar(10) NOT NULL default "",
    `cron_name` varchar(50) NOT NULL default "",
    `time` int(11)
)');