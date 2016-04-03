<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
//hook in to extension/users page
//prepare page

/* Right, so why is this here?
 * Well because on strange instances of FreePBX installs the function userman doesn't exist
 * Who knows why? Its really weird
 */
if (!function_exists('setup_userman')) {
	global $amp_conf;
	$um = module_getinfo('userman', MODULE_STATUS_ENABLED);
	if(file_exists($amp_conf['AMPWEBROOT'].'/admin/modules/userman/functions.inc.php') && (isset($um['userman']['status']) && $um['userman']['status'] === MODULE_STATUS_ENABLED)) {
		include_once($amp_conf['AMPWEBROOT'].'/admin/modules/userman/functions.inc.php');
	} else {
		return false;
	}
}

setup_userman()->registerHook('updateUser', 'bria_hook_userman_updateUser');
setup_userman()->registerHook('addUser', 'bria_hook_userman_updateUser');
setup_userman()->registerHook('delUser', 'bria_hook_userman_delUser');


if (!class_exists('PestBria')) {
	include_once('PestBria.php');
}

function bria_hook_userman_updateUser($uid, $display, $data) {
	if (isset($_POST['bria|enable']) && $display == 'userman') {
		$briaEnable = ($_POST['bria|enable'] == 'true') ? 1 : 0;
		setup_userman()->setModuleSettingByID($uid, 'bria', 'enabled', $briaEnable);

		//We need to save the password for this user in plain text
		if(!empty($data['password'])) {
			setup_userman()->setModuleSettingByID($uid, 'bria', 'password', $data['password']);
		}

		//Try to create the Bria user which will delete it if it exists and recreate it
		$usermanUser = setup_userman()->getUserByID($uid);

		if ($briaEnable === 1) {
			//We need to pass in the previous username to ensure we delete the correct one
			bria_create_device($data['username'], $data['prevUsername'], $uid, $usermanUser['default_extension']);
		} else {
			bria_delete_device($data['prevUsername'], $usermanUser['default_extension']);
		}
		needreload();
	}
}

function bria_hook_userman_delUser($uid, $display, $data) {
	//Delete the device associated with the user
	bria_delete_device($data['prevUsername']);
	needreload();
}

function bria_hook_userman() {
	if (isset($_REQUEST['action'])) {
		switch ($_REQUEST['action']) {
			case 'showuser':
				$ccsUsername = bria_get_setting('username');
				$ccsPassword = bria_get_setting('password');
				$ccsIpAddress = bria_get_setting('ipaddr');

				if (!empty($ccsUsername) && !empty($ccsPassword) && !empty($ccsIpAddress)) {
					$briaData = setup_userman()->getModuleSettingByID($_REQUEST['user'], 'bria', 'enabled');

					$enabled = ($briaData['enabled']) ? true : false;

					return load_view(dirname(__DIR__).'/views/userman_hook.php', array("enabled" => $enabled));
				}
			break;
		}
	}
}
