<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

echo "dropping table calllimit..";
sql('DROP TABLE IF EXISTS `calllimit`');
echo "done<br>\n";

echo "dropping table calllimit_usage..";
sql('DROP TABLE IF EXISTS `calllimit_usage`');
echo "done<br>\n";

?>
