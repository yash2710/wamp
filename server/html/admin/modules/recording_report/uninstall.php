<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
$cron_out = array();

exec("/usr/bin/crontab -l | grep -v ^#\ | grep -v backuprecordings.php | grep -v backuprecordingsemail.php",$cron_out,$ret1);

$cron_out_string = implode("\n",$cron_out);

exec("/bin/echo '$cron_out_string' | /usr/bin/crontab -",$out_arr,$ret2);
