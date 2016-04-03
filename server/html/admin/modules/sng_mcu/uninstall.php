<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}

echo "dropping service tables...\n";
$sql[] = "DROP TABLE IF EXISTS `sng_mcu_details`";
$sql[] = "DROP TABLE IF EXISTS `sng_mcu_entries`";

foreach ($sql as $q) {
        $result = $db->query($q);
        if($db->IsError($result)){
                die_freepbx($result->getDebugInfo());
        }
}
