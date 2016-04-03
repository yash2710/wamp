#!/usr/bin/php -q
<?php

/**********************************************************************
 *               Schmoozecom Voicemail Notification                   *
 *                    Copyright (C) 2009-2013                         *
 *                       Schmooze Com, Inc                            *
 **********************************************************************/

if(file_exists("/var/lib/asterisk/agi-bin/LoadLicenseIfExists.php")) {
    include_once("/var/lib/asterisk/agi-bin/LoadLicenseIfExists.php");
    include('enc/vmnotify-audit.php');
}
