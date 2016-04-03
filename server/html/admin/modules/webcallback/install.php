<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
global $amp_conf;
$sql[] = 'CREATE TABLE IF NOT EXISTS `webcallback` (
		`id` int(11) NOT NULL auto_increment,
		`name` varchar(50) default NULL,
		`dest` varchar(150) default NULL,
		`cidprepend` varchar(25) default NULL,
		`accountcode` int(11) default NULL,
		`button_attr` text,
		`dialpad_attr` text,
		`patterns` text,
		`error_msg` text,
		`valid_msg` text,
		`invalid_msg` text,
		`icon` varchar(25) default NULL,
		PRIMARY KEY  (`id`)
		)';

foreach ($sql as $statement){
	$check = $db->query($statement);
	if ($db->IsError($check)){
		die_freepbx( "Can not execute $statement : " . $check->getMessage() .  "\n");
	}
}

//add valid/invalid messages
if (!$db->getAll('SHOW COLUMNS FROM webcallback WHERE FIELD = "valid_msg"')) {
	sql('ALTER TABLE webcallback ADD valid_msg text AFTER error_msg, ADD invalid_msg text AFTER valid_msg');
	$valid = _('Please hold while we call you back.');
	$invalid = _('That number is invalid as a callback number');
	$error = _('Oops, something went wrong!');
	sql('UPDATE webcallback SET valid_msg = "' . $valid . '"');
	sql('UPDATE webcallback SET invalid_msg = "' . $invalid . '"');
	sql('UPDATE webcallback SET error_msg = "' . $error . '"');
}

//add num prepend
if (!$db->getAll('SHOW COLUMNS FROM webcallback WHERE FIELD = "numprepend"')) {
	sql('ALTER TABLE webcallback ADD numprepend varchar(50) after cidprepend');
}
$target = $amp_conf['AMPWEBROOT'] . '/wcb.php';
if (!file_exists($target)) {
	symlink($amp_conf['AMPWEBROOT'] . '/admin/modules/webcallback/wcb.php', $target);
}
?>
