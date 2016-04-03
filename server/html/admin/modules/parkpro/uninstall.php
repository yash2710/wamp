<?php
    
$sql = "DROP TABLE IF EXISTS parkplus_device";
sql($sql);

$sql = "DROP TABLE IF EXISTS parkplus_announce";
sql($sql);

if ($db->getAll('SHOW TABLES LIKE "parkplus"')) {
    $sql = "DELETE FROM parkplus WHERE defaultlot != 'yes'";
    sql($sql);
}