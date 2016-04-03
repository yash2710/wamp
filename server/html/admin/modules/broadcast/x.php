<?php

$fh = fopen("/tmp/foo.csv", "r");

include 'enc/Importer.class.php';
$i = new Importer();

print_r($i->importCSV($fh));
