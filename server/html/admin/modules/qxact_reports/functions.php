<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

if(file_exists("/var/lib/asterisk/agi-bin/LoadLicenseIfExists.php")) {
    include_once("/var/lib/asterisk/agi-bin/LoadLicenseIfExists.php");
    include('functions.inc/functions.php');
	include('functions.inc/report_queries.php');
}

function qxact_admin_main_page($additional_title = false)
{
	if ($additional_title)
	{
		echo "<h2>QXact Report Templates - $additional_title</h2>";
	}
	else
	{
		echo "<h2>QXact Report Templates</h2>";
	}

	//Right Nav Bar Begins Here
	echo '<div class="rnav"><ul>';
	echo '<li><a href="config.php?type=tool&display=qxact_admin&action=new">New Template</a></li>';
	echo '<!-- <li><a href="config.php?type=tool&display=qxact_admin&action=settings">Settings</a></li> -->';
	echo '<hr>';


	$results = sql("SELECT id, name FROM qxact_reports ORDER BY name ASC","getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
            echo '<li><a href="config.php?type=tool&display=qxact_admin&action=edit&id='.$result['id'].'">'.$result['name'].'</a></li>';
        }
	}
	echo '</ul></div>';

}


?>