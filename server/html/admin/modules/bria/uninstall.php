<?php /* $Id$ */

out(_("Uninstalling Bria Cloud Solutions!"));
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

$sql="DROP TABLE bria_settings";
out(_("Removing Database!"));
sql($sql);
