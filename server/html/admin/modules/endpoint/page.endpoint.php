<?php if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
if(file_exists("/var/lib/asterisk/agi-bin/LoadLicenseIfExists.php")) {
    include_once("/var/lib/asterisk/agi-bin/LoadLicenseIfExists.php");
}
global $load_license;
if ($load_license === true) {
	if ($_REQUEST['action'] == 'ajax') {
		echo 'he';
	}
        include_once('views/page.endpoint.php');
} else {
        echo sysadmin_get_sales_html('endpoint');
}
