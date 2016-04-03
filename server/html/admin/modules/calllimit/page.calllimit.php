<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}

if (!defined('ZEND_LICENSE_LOADED')) {
	echo sysadmin_get_sales_html('calllimit');        
} else {
        include('views/page.calllimit.php');
}
