<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
global $db;

$sql[]='ALTER TABLE `fax_users` 
        DROP `faxlocalstore`, 
        DROP `faxstationid`, 
        DROP `faxheader`, 
	      DROP `faxcovername`,
	      DROP `faxcovertel`,
	      DROP `faxcoveremail`;';


$sql[]='DROP TABLE `fax_store`;';
$sql[] = 'DROP TABLE `faxpro_hook_core`';

foreach ($sql as $statement){
	$check = $db->query($statement);
	if (DB::IsError($check)){
		die_freepbx( "Can not execute $statement : " . $check->getMessage() .  "\n");
	}
}

unlink($amp_conf['AMPWEBROOT'].'/recordings/modules/faxpro.module');
@unlink($amp_conf['AMPWEBROOT'].'/admin/images/companylogo.jpg');
@unlink($amp_conf['ASTAGIDIR'].'/enc/fax.agi');

$sql = "DELETE FROM fax_details WHERE `key` = 'prefix'";
sql($sql);
?>
