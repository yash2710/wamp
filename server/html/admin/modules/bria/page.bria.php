<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
$get_vars = array(
	'action'	=> '',
	'submit'	=> '',
	'username' => '', //Bria API Username
	'password' => '', //Bria API Password
	'prefix' => '', //Bria Device Prefix
	'ipaddr' => '', //IP or FQDN of this PBX
);

foreach ($get_vars as $k => $v) {
	$var[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}

$action = $var['action'];

//action actions
switch ($action) {
	case 'save':
		//Unset any settings not needed
		unset($var['action'], $var['submit']);

		bria_set_multiple_settings($var);
		break;
}

//view action
switch ($action) {
	case 'edit':
	case 'save':
	default:
		$groupResponse = null;

		$bria_admin_settings['limits'] = array(
			'numUsers' =>	0,
			'maxUsers' =>	0,
			'deviceLimit' => 0,
			'deviceCount' => 0,
			'desktopLimit' => 0,
			'desktopCount' => 0,
			'phoneLimit' => 0,
			'phoneCount' => 0,
			'tabletLimit' => 0,
			'tabletCount' => 0,
		);

		//Get bria admin information and populate our view
		$bria_admin_settings['username'] = bria_get_setting('username');
		$bria_admin_settings['password'] = bria_get_setting('password');
		$bria_admin_settings['prefix'] = bria_get_prefix();
		$bria_admin_settings['ipaddr'] = bria_get_setting('ipaddr');
		$bria_admin_settings['users'] = bria_get_users_enabled();
		$bria_admin_settings['groupname'] = bria_get_setting('groupname');

		if (!empty($bria_admin_settings['username']) && !empty($bria_admin_settings['password'])) {
			try {
				$bria = new PestBria($bria_admin_settings['username'], $bria_admin_settings['password']);

				$groupResponse = $bria->getGroup(array('groupName' => 'all'));

				if ($groupResponse->attributes()->resultCode) {
					$bria_admin_settings['message'] = array(
						'type' => 'danger',
						'message' => $groupResponse->attributes()->resultCode . ' - ' . $groupResponse[0],
					);
					$bria_admin_settings['connection'] = _('Connected with errors');
				}
			} catch (Pest_Unauthorized $e) {
				$bria_admin_settings['message'] = array(
					'type' => 'danger',
					'message' => _('Account Login/Provisioning Code is incorrect. Please try again.'),
				);
			} catch (Exception $e) {
				$bria_admin_settings['message'] = array(
					'type' => 'danger',
					'message' => $e->getMessage(),
				);
			}

			if (empty($bria_admin_settings['message'])) {
				$bria_admin_settings['connection'] = _('Connected');
			} else if (!empty($bria_admin_settings['message']) && empty($bria_admin_settings['connection'])) {
				$bria_admin_settings['connection'] = _('Disconnected');
			}

			if (is_object($groupResponse) && isset($groupResponse->CcsUserGroup)) {
				$tmpGroupName = $groupResponse->CcsUserGroup->attributes()->groupName;
				//check if we have a groupname or if the settings don't match from what we have in the database
				if (empty($bria_admin_settings['groupname']) || $bria_admin_settings['groupname'] != $tmpGroupName) {
					//ensure we set it in the db for future use
					$bria_admin_settings['groupname'] = $tmpGroupName;
					bria_set_setting('groupname', $bria_admin_settings['groupname']);
				}
				$bria_admin_settings['limits'] = array(
					'numUsers' =>	$groupResponse->CcsUserGroup->attributes()->numUsers,
					'maxUsers' =>	$groupResponse->CcsUserGroup->attributes()->maxUsers,
					'deviceLimit' => $groupResponse->CcsUserGroup->CcsGroupLimit->attributes()->deviceLimit,
					'deviceCount' => $groupResponse->CcsUserGroup->CcsGroupLimit->attributes()->deviceCount,
					'desktopLimit' => $groupResponse->CcsUserGroup->CcsGroupLimit->attributes()->desktopLimit,
					'desktopCount' => $groupResponse->CcsUserGroup->CcsGroupLimit->attributes()->desktopCount,
					'phoneLimit' => $groupResponse->CcsUserGroup->CcsGroupLimit->attributes()->phoneLimit,
					'phoneCount' => $groupResponse->CcsUserGroup->CcsGroupLimit->attributes()->phoneCount,
					'tabletLimit' => $groupResponse->CcsUserGroup->CcsGroupLimit->attributes()->tabletLimit,
					'tabletCount' => $groupResponse->CcsUserGroup->CcsGroupLimit->attributes()->tabletCount,
				);
			}
		}

		echo load_view(dirname(__FILE__) . '/views/admin.php', $bria_admin_settings);
		break;
}
