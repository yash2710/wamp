<?php /* $Id$ */
global $db;

out(_("Installing Bria Cloud Solutions!"));
if (! function_exists("out")) {
	function out($text) {
		echo $text."<br />";
	}
}

if (! function_exists("outn")) {
	function outn($text) {
		echo $text;
	}
}

$sql = "CREATE TABLE IF NOT EXISTS `bria_settings` (
	`key` VARCHAR( 255 ) NOT NULL UNIQUE ,
	`value` TEXT NOT NULL
)";
sql($sql);

if (!$db->getOne("SELECT value FROM bria_settings WHERE `key` = 'device_prefix'")) {
	sql("INSERT INTO bria_settings (`key`, `value`) VALUES('device_prefix','999')");
}
