#!/usr/bin/php -q
<?php

/**********************************************************************
 *               Schmoozecom Voicemail Notification                   *
 *                    Copyright (C) 2009-2013                         *
 *                       Schmooze Com, Inc                            *
 **********************************************************************/

# set some variables we can use later
$context = $argv[1];
$mailbox = $argv[2];
$newvmcount = $argv[3];
$oldvmcount = $argv[4];
$urgvmcount = $argv[5];

// If there are no new voicemails then we don't need to proceed
//
if ($newvmcount == 0 && $urgvmcount == 0) {
	exit(0);
}

// There are new messages so let's license check which bootstraps and then proceed
//
$bootstrap_settings['skip_astman'] = true;
if(file_exists("/var/lib/asterisk/agi-bin/LoadLicenseIfExists.php")) {
        include_once("/var/lib/asterisk/agi-bin/LoadLicenseIfExists.php");    
	include('enc/vmnotify-newvm.php');
}
