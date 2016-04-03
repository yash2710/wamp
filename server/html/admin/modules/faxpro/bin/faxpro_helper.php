#!/usr/bin/php -q
<?php
$restrict_mods = array('fax' => true, 'faxpro' => true);
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
	include_once('/etc/asterisk/freepbx.conf');
}
require('enc/faxpro_helper.php');

?>