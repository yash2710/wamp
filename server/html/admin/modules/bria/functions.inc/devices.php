<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function bria_delete_device($username, $extension) {
	$prefix = bria_get_prefix();
	$ccsUsername = bria_get_setting('username');
	$ccsPassword = bria_get_setting('password');
	$ccsGroupName = bria_get_setting('groupname');

	//if no ccs username and password exit
	if (empty($ccsUsername) || empty($ccsPassword) || empty($ccsGroupName)) {
		return false;
	}

	core_devices_del($prefix.$extension);

	//Delete Bria device on the server
	$bria = new PestBria($ccsUsername, $ccsPassword);

	try {
		$getResponse = $bria->getUser(array(
			'userName' => $username . '@' . $ccsGroupName,
		));
	} catch (Exception $e) {
		return false;
	}

	//Delete a User if they already exist
	if (is_object($getResponse) && isset($getResponse->CcsUser) && $getResponse->CcsUser->attributes()->userName) {
		try {
			$delResponse = $bria->deleteUser(array(
				'userName' => $getResponse->CcsUser->attributes()->userName,
			));
		} catch (Exception $e) {
			return false;
		}

		if ($bria->lastStatus() === 200) {
			return true;
		}
	}

	return false;
}

function bria_create_device($username, $prevUsername, $uid, $extension) {
	$prefix = bria_get_prefix();
	$ccsUsername = bria_get_setting('username');
	$ccsPassword = bria_get_setting('password');
	$ccsIpAddress = bria_get_setting('ipaddr');
	$ccsGroupName = bria_get_setting('groupname');

	//if no ccs username and password exit
	if (empty($ccsUsername) || empty($ccsPassword) || empty($ccsGroupName) || empty($ccsIpAddress)) {
		return false;
	}

	$usr = core_users_get($extension);
	$dev = core_devices_get($prefix.$extension);

	//get the original extensions information
	$extensionDev = core_devices_get($extension);

	if (empty($usr)) {
		return false;
	}

	if (empty($dev)) {
		$res = $_REQUEST;

		//Override the device page here
		$settings = array(
			'icesupport' => 'yes',
			'transport' => 'udp',
			'dial' => 'SIP/'.$prefix.$extension,
			'qualify' => 'yes',
			'host' => 'dynamic',
			'type' => 'friend',
			'nat' => 'yes',
			'port' => '5060',
			'trustrpid' => 'yes',
			'qualifyfreq' => 60,
			'encryption' => 'no',
			'callgroup' => '',
			'pickupgroup' => '',
			'disallow' => '',
			'avpf' => 'no',
			'transport' => 'udp',
			'allow' => '',
			'mailbox' => $extension . '@device',
			'deny' => '0.0.0.0/0.0.0.0',
			'permit' => '0.0.0.0/0.0.0.0',
			'dtmfmode' => 'rfc2833',
			'secret' => $extensionDev['secret'],
			'sendrpid' => 'no',
			'canreinvite' => 'no',
			'context' => 'from-internal'
		);

		foreach ($settings as $key => $value) {
			$_REQUEST['devinfo_'.$key] = $value;
		}

		core_devices_add($prefix.$extension, 'sip', '', 'fixed', $extension, $usr['name'].' Bria Client');

		foreach ($settings as $key => $value) {
			if (!empty($res['devinfo_'.$key])) {
				$_REQUEST['devinfo_'.$key] = $res['devinfo_'.$key];
			}
		}

	}

	//Create Bria device on the server
	$bria = new PestBria($ccsUsername, $ccsPassword);

	//Determine if the user already exists
	try {
		$getResponse = $bria->getUser(array(
			'userName' => $prevUsername . '@' . $ccsGroupName,
		));
	} catch (Exception $e) {
		return false;
	}

	//If user already exists, delete them so that we can recreate them
	if ($bria->lastStatus() === 200 && isset($getResponse->CcsUser) && isset($getResponse->CcsUser->attributes()->userName)) {
		$delResponse = $bria->deleteUser(array(
			'userName' => $getResponse->CcsUser->attributes()->userName,
		));

		if ($bria->lastStatus() !== 200) {
			return false;
		}
	}

	//Get the original extension information for the sip secret
	$dev = core_devices_get($extension);

	//Get the user password from userman
	$userPassword = setup_userman()->getModuleSettingByID($_REQUEST['user'], 'bria', 'password');

	//Add Bria user
	$user = array(
		'userName' => $username  . '@' . $ccsGroupName,
		'password' => $userPassword, //Grab the plain text password to use
		'profileName' => 'sip.only',
	);

	$attributes = array(
		'account1Sip.credentials.authorizationName' => $prefix.$extension,
		'account1Sip.accountName' => $usr['name'],
		'account1Sip.credentials.displayName' => $usr['name'],
		'account1Sip.credentials.password' => $dev['secret'],
		'account1Sip.credentials.username' => $prefix.$extension,
		'account1Sip.domain' => $ccsIpAddress,
	);

	//Get the voicemail feature code from feature codes and set it if active
	$fcc = new featurecode('voicemail', 'myvoicemail');
	$voicemailFeatureCodeActive = $fcc->isEnabled();
	if ($voicemailFeatureCodeActive) {
		$voicemailFeatureCode = $fcc->getCode();
		$attributes['account1Sip.voicemailNumber'] = $voicemailFeatureCode;
	}

	if (function_exists('xmpp_users_get')) {

		$xmppUser = xmpp_users_get($uid);

		if (!empty($xmppUser['username']) && !empty($xmppUser['password'])) {

			$xmppDomain = xmpp_opts_get('domain');

			$user['profileName'] = 'sip.xmpp';

			$attributes['account2Xmpp.credentials.displayName'] = $usr['name'];
			$attributes['account2Xmpp.credentials.username'] = $xmppUser['username'];
			$attributes['account2Xmpp.credentials.password'] = $xmppUser['password'];
			$attributes['account2Xmpp.domain'] = $xmppDomain;
		}
	}

	$attributes['account.notification.administratorName'] = 'FreePBX';

	$user = array_merge($user, $attributes);

	try {
		$addResponse =  $bria->addUser($user);
	} catch (Exception $e) {
		return false;
	}

	if ($bria->lastStatus() === 200) {
		return true;
	}

	return false;
}
