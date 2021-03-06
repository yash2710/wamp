<?php
//TODO: set intial server keys
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
require_once(dirname(__FILE__) . '/functions.inc.php');

global $db;

$sql[]='CREATE TABLE IF NOT EXISTS `restapi_general` (
  `keyword` varchar(50),
  `value` varchar(150) default NULL,
  UNIQUE KEY `keyword` (`keyword`)
);';

$sql[]='CREATE TABLE IF NOT EXISTS `restapi_log_event_details` (
  `id` int(11) default NULL AUTO_INCREMENT,
  `e_id` int(11) default NULL,
  `time` int(11) default NULL,
  `event` varchar(150) default NULL,
  `data` text,
  `trigger` text,
   UNIQUE KEY `id` (`id`),
   KEY `e_id` (`e_id`)
);';

$sql[]='CREATE TABLE IF NOT EXISTS `restapi_log_events` (
  `id` int(11) default NULL AUTO_INCREMENT,
  `time` int(11) default NULL,
  `token` varchar(75) default NULL,
  `signature` varchar(150) default NULL,
  `ip` varchar(20) default NULL,
  `server` varchar(75) default NULL,
   UNIQUE KEY `id` (`id`),
   KEY `time` (`time`),
   KEY `token` (`token`)
);';

$sql[]='CREATE TABLE IF NOT EXISTS `restapi_token_details` (
  `token_id` int(11) default NULL,
  `key` varchar(50) default NULL,
  `value` varchar(150) default NULL
);';

$sql[]='CREATE TABLE IF NOT EXISTS `restapi_tokens` (
  `id` int(11) default NULL AUTO_INCREMENT,
  `name` varchar(150) default NULL,
  `desc` varchar(250) default NULL,
  UNIQUE KEY `id` (`id`)
);';

$sql[] = 'CREATE TABLE IF NOT EXISTS `restapi_token_user_mapping` (
  `user` varchar(25) default NULL,
  `token_id` int(11) default NULL
);';

$firstinstall = false;
$q = $db->query('SELECT * FROM restapi_general;');
if (DB::isError($q)) {
	$firstinstall = true;

	$sql[] = 'INSERT IGNORE INTO `restapi_general` VALUES
	  ("status", "normal"),
	  ("token", "' . restapi_tokens_generate() . '"),
	  ("tokenkey", "' . restapi_tokens_generate() . '");';
}

foreach ($sql as $statement){
        $check = $db->query($statement);
        if (DB::IsError($check)){
                die_freepbx( "Can not execute $statement : " . $check->getMessage() .  "\n");
        }
}

$sql = "SHOW KEYS FROM restapi_log_event_details WHERE Key_name='e_id'";
$check = $db->getOne($sql);
if (empty($check)) {
	$sql = "ALTER TABLE restapi_log_event_details ADD KEY `e_id` (`e_id`)";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		out(_("Unable to add index to e_id field in restapi_log_event_details table"));
		freepbx_log(FPBX_LOG_ERROR, "Failed to add index to e_id field in the restapi_log_event_details table");
	} else {
		out(_("Adding index to e_id field in the restapi_log_event_details table"));
	}
}

$sql = "SHOW KEYS FROM restapi_log_events WHERE Key_name='time'";
$check = $db->getOne($sql);
if (empty($check)) {
	$sql = "ALTER TABLE restapi_log_events ADD KEY `time` (`time`), ADD KEY `token` (`token`)";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		out(_("Unable to add index to time field in restapi_log_events table"));
		freepbx_log(FPBX_LOG_ERROR, "Failed to add index to time field in the restapi_log_events table");
	} else {
		out(_("Adding index to time field in the restapi_log_events table"));
	}
}

if ($firstinstall) {
	/* Initial installation */
	$users = core_users_list();
	foreach ($users as $u) {
		$user = $u[0];
		if ($user && !restapi_user_get_user_tokens($user)) {
			$token = restapi_tokens_get('');
			$token['assoc_user'] = $user;
			$token['desc'] = _('Autogenerated token on installation');
			$token['name'] = _('User') . ' ' . $user . ' (' . _('autogen') . ')';
			$token['token'] = restapi_tokens_generate();
			$token['tokenkey'] = restapi_tokens_generate();
			$token['modules'] = array('*');
			$token['users'] = array($user);
			$token['token_status'] = 'enabled';

			restapi_tokens_put($token);
		}
	}
}

mkdir($amp_conf['AMPWEBROOT'] . '/restapi');
symlink(dirname(__FILE__) . '/rest.php', $amp_conf['AMPWEBROOT'] . '/restapi/rest.php');

if(!function_exists('setup_userman')) {
    out(_('Usermanager Module is missing or disabled'));
    return false;
}

$mod_info = module_getinfo('restapi');
if(!empty($mod_info['restapi']) && version_compare($mod_info['restapi']['dbversion'],'2.11.1.2','<')) {
    out('Migrating Token Users to User Manager');
    $sql = 'SELECT * FROM restapi_token_user_mapping';
    $users = sql($sql,'getAll',DB_FETCHMODE_ASSOC);
    if(!empty($users)) {
        $usermapping = array();
        $userman = setup_userman();
        $umusers = array();
        $umusersn = array();
        foreach($userman->getAllUsers() as $user) {
            $umusersn[] = $user['username'];
            if($user['default_extension'] == 'none') {
                continue;
            }
            $umusers[$user['default_extension']] = $user['id'];
        }
        foreach($users as $user) {
            $sql = "SELECT * FROM restapi_tokens WHERE `id` = ".$user['token_id'];
            $tokenDetails = sql($sql,'getAll',DB_FETCHMODE_ASSOC);
            $ucpuser = $user['user'];
            out('Found Token '.$tokenDetails[0]['name']);
            if(empty($usermapping[$user['user']]['id'])) {
                if(isset($umusers[$user['user']])) {
                    $id = $umusers[$user['user']];
                    $uInfo = $userman->getUserByID($id);
                    $userman->updateUser($uInfo['username'], $uInfo['username'], $uInfo['default_extension'], $tokenDetails[0]['desc']);
                } else {
                    out('Creating a User Manager User called '. $ucpuser .' for token '.$tokenDetails[0]['name']);
                    $output = $userman->addUser($ucpuser, bin2hex(openssl_random_pseudo_bytes(6)), $user['user'], $tokenDetails[0]['desc']);
                    if(!$output['status']) {
                        out('User confliction detected, attempting to autogenerate a username for token '. $tokenDetails[0]['name']);
                        $output = $userman->addUser(bin2hex(openssl_random_pseudo_bytes(6)), bin2hex(openssl_random_pseudo_bytes(6)), $user['user'], $tokenDetails[0]['desc']);
                        if(!$output['status']) {
                            out('Username auto generation failed, skipping token '.$tokenDetails[0]['name']);
                            continue;
                        }
                    }
                    $id = $output['id'];
                }
            } else {
                out('Adding token '.$tokenDetails[0]['name'].' to  User '. $ucpuser);
                $id = $usermapping[$user['user']]['id'];
            }
            $sql = "UPDATE restapi_token_user_mapping SET user = '".$id."' WHERE user = '".$user['user']."'";
            sql($sql);
            $sql = "SELECT value FROM restapi_token_details WHERE `key` = 'users' AND token_id = ".$user['token_id'];
            $uljson = sql($sql,'getOne');
            $ul = json_decode($uljson,true);
            if($ul[0] == "*") {
                $ul = array();
                foreach(core_users_list() as $list) {
                    $ul[] = $list[0];
                }
            }
            $devices = $userman->getAssignedDevices($id);
            $devices = !empty($devices) ? $devices : array();
            foreach($ul as $d) {
                if(!in_array($d,$devices)) {
                    $devices[] = $d;
                }
            }
            out('Attaching devices '.implode(',',$ul).' from token '.$tokenDetails[0]['name'].' to user '.$ucpuser);
            $userman->setAssignedDevices($id,$devices);

            $usermapping[$user['user']]['id'] = $id;
        }
    }
}
