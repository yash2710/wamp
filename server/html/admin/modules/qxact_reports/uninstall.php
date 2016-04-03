<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

//remove all crons
edit_crontab('import_queue_data.php');
