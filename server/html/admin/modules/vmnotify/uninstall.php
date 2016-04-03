<?php 
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
exec('rm -f /var/lib/asterisk/agi-bin/vmnotify*');
exec('rm -f /var/lib/asterisk/agi-bin/enc/vmnotify-*');

?>
