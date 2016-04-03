#!/usr/bin/env php
<?php
$restrict_mods = true;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
          include_once('/etc/asterisk/freepbx.conf');
}
if(file_exists($amp_conf['ASTAGIDIR'] . "/LoadLicenseIfExists.php")) {
    include_once($amp_conf['ASTAGIDIR'] . "/LoadLicenseIfExists.php");
    require_once('functions.inc/import_queue_data.php');
}
?>
