<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

global $amp_conf;

$agidir = $amp_conf['ASTAGIDIR'];
if (!$agidir) {
        $agidir = "/var/lib/asterisk/agi-bin";
}

$ll = "$agidir/LoadLicenseIfExists.php";

if (file_exists($ll)) {
        include $ll;
}

// Always include, doesn't matter about licences, as we check before loading
// the one licenced file.
include dirname(__FILE__) . '/functions.inc/general.php';

