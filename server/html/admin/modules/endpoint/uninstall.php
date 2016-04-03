<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
global $db, $amp_conf;

echo "dropping endpoint tables...\n";

sql("DROP TABLE `endpoint_basefiles`");
sql("DROP TABLE `endpoint_brand`");
sql("DROP TABLE `endpoint_buttons`");
sql("DROP TABLE `endpoint_customExt`");
sql("DROP TABLE `endpoint_extensions`");
sql("DROP TABLE `endpoint_firmware`");
sql("DROP TABLE `endpoint_global`");
sql("DROP TABLE `endpoint_images`");
sql("DROP TABLE `endpoint_models`");
sql("DROP TABLE `endpoint_templates`");
sql("DROP TABLE `endpoint_timezones`");
sql("DROP TABLE `endpoint_xml`");

if(file_exists($amp_conf['AMPWEBROOT'] . '/admin/.htaccess')){
	$htaccess =  file_get_contents($amp_conf['AMPWEBROOT'] . '/admin/.htaccess');
	preg_replace('/php_value max_input_vars 5000/', '', $htaccess);
	file_put_contents($amp_conf['AMPWEBROOT'] . '/admin/.htaccess', $htaccess);
}
echo "done<br>\n";