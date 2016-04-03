#!/usr/bin/php -q
<?php
###################################################################
# AGI for recording voicemail greetings
#
# Copyright 2014 Schmooze Com, Inc.
###################################################################

require_once('phpagi.php');

if ($argc < 3) {
	exit(1);
}

$agi = new AGI();

$path = $argv[1];
$type = $argv[2];

$recpath = $path . '/' . $type;

$res = $agi->record_file($recpath, 'wav', '#', -1, '', true, NULL);

if (chr($res['result']) == '#') {
	exec('/usr/bin/sox ' . $recpath . '.wav -c 1 -r 8000 -g -t wav ' . $recpath . '.WAV');

	$agi->exec('Playback', 'vm-msgsaved');
}

?>
