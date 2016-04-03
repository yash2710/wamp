<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
// Uninstall Cron

edit_crontab('areminder-manager.php','');

?>

