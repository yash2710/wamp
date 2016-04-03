<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
global $db;

$inst=$db->getAll('SELECT `faxlocalstore` FROM `fax_users`');
if($db->IsError($inst)){//assume that this is the first install
  $sql[]='ALTER TABLE `fax_users`
  	ADD `faxlocalstore` VARCHAR(10) NULL,
  	ADD `faxstationid` VARCHAR(25) NOT NULL,
  	ADD `faxheader` VARCHAR(75) NOT NULL,
  	ADD `faxcovername` VARCHAR(50) NULL ,
  	ADD `faxcovertel` VARCHAR(50) NULL ,
  	ADD `faxcoveremail` VARCHAR(75) NULL';
  //note: maxaction could never be set at this point, but were just going to double check anyway.
  $maxaction='';
  $maxaction=$db->getOne('SELECT value FROM fax_details WHERE `key` = "maxaction"');
	if(!$maxaction){//set the default max fax pages to 1000
		$sql[]="REPLACE INTO fax_details (`key`, `value`) VALUES ('maxaction','delete')";
		$sql[]="REPLACE INTO fax_details (`key`, `value`) VALUES ('maxpages','1000')";
	}
}

$inst=$db->getAll('SELECT `faxretries` FROM `fax_users`');
if($db->IsError($inst)){//assume that this column doesn't exist
  $sql[]='ALTER TABLE `fax_users` ADD `faxretries` VARCHAR(10) NULL';
}

$inst=$db->getAll('SELECT `emailresults` FROM `fax_users`');
if($db->IsError($inst)){//assume that this column doesn't exist
  $sql[]='ALTER TABLE `fax_users` ADD `emailresults` VARCHAR(10) NULL';
}


$sql[]='CREATE TABLE IF NOT EXISTS `fax_store` (
	`faxid` varchar(25) default NULL,
  `user` varchar(25) default NULL,
  `dir` varchar(10) default NULL,
  `date` varchar(12) default NULL,
  `header` varchar(80) default NULL,
  `stationid` varchar(50) default NULL,
  `callid` varchar(75) default NULL,
  `dest` varchar(20) default NULL,
  `status` varchar(255) default NULL,
  `pages` varchar(10) default NULL,
  `file` varchar(100) default NULL,
  `new` varchar(10) default NULL,
  PRIMARY KEY(faxid)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;';

$sql[] = 'ALTER TABLE fax_store CHANGE COLUMN status status varchar(255)';
$sql[] = 'ALTER TABLE fax_store CHANGE COLUMN user user varchar(25)';

$inst=$db->getAll('SELECT `finished` FROM `fax_store`');
if($db->IsError($inst)){//assume that this column doesn't exist
  $sql[]='ALTER TABLE `fax_store` ADD `finished` VARCHAR(2) DEFAULT 1';
}

//during an upgrade force finish all faxes
$sql[] = "UPDATE fax_store SET finished = 1 WHERE finished != 1";

$sql[] = 'CREATE TABLE IF NOT EXISTS `faxpro_hook_core` (
	`page` varchar(255) NOT NULL DEFAULT "",
	`id` varchar(255) NOT NULL DEFAULT "",
	`status` varchar(255) NOT NULL DEFAULT "",
	PRIMARY KEY (`page`,`id`)
)';
foreach ($sql as $statement){
	$check = $db->query($statement);
	if ($db->IsError($check)){
		die_freepbx( "Can not execute $statement : " . $check->getMessage() .  "\n");
	}
}


@mkdir($amp_conf['ASTAGIDIR'].'/enc');
@copy($amp_conf['AMPWEBROOT'].'/admin/modules/faxpro/agi-bin-install/enc/fax.agi',$amp_conf['ASTAGIDIR'].'/enc/fax.agi');
if (file_exists($amp_conf['AMPWEBROOT'].'/admin/images/companylogo.jpg')) {
	$logo = $amp_conf['ASTSPOOLDIR'] . '/faxpro/images/companylogo.jpg';
	mkdir(dirname($logo), 0755, true);
	rename($amp_conf['AMPWEBROOT'].'/admin/images/companylogo.jpg', $logo);

}
?>
