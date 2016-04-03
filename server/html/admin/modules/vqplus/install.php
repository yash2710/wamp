<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

$modversions = array(
	"announcement" => "2.11.0.1",
	"callback" => "2.11.0.1",
	"callrecording" => "2.11.0.5",
	"cdr" => "2.11.0.6",
	"core" => "2.11.0.19",
	"dahdiconfig" => "2.11.40",
	"endpoint" => "2.11.0.1.46",
	"framework" => "2.11.0.31",
	"hotelwakeup" => "2.11.2",
	"languages" => "2.11.0.1",
	"miscdests" => "2.11.0.1",
	"parking" => "2.11.0.14",
	"parkpro" => "2.11.0.5",
	"queueprio" => "2.11.0.1",
	"superfecta" => "2.11.11",
	"timeconditions" => "2.11.0.4"
);

$mods = modulelist::create($db);

$version_check = true;
foreach ($modversions as $module => $version) {
	if (isset($mods->module_array[$module])) {
		if (!version_compare($mods->module_array[$module]['version'], $version, "ge")) {
			out($mods->module_array[$module]['name'] . " must be upgraded to version " . $version . " or higher.");
			$version_check = false;
		}
	}
}
if (!$version_check) {
	return false;
}

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql['virtual_queue_config'] = "
	CREATE TABLE IF NOT EXISTS virtual_queue_config (
		id INTEGER NOT NULL $autoincrement,
		name VARCHAR(40) NOT NULL DEFAULT '',
		priority INT DEFAULT NULL,
		position INT DEFAULT NULL,
		min_penalty INT DEFAULT NULL,
		max_penalty INT DEFAULT NULL,
		rule_id INT DEFAULT NULL,
		joinannounce_id INT DEFAULT NULL,
		agentannounce_id INT DEFAULT NULL,
		music VARCHAR(100) DEFAULT NULL,
		maxwait VARCHAR(8) DEFAULT NULL,
		cidpp VARCHAR(100) NOT NULL DEFAULT '',
		alertinfo VARCHAR(254) NOT NULL DEFAULT '',
		language VARCHAR(50) NOT NULL DEFAULT '',
		callconfirm_id INT DEFAULT NULL,
		dest VARCHAR(100) NOT NULL DEFAULT '',
		cdest VARCHAR(100) NOT NULL DEFAULT '',
		adest VARCHAR(100) NOT NULL DEFAULT '',
		gotodest VARCHAR(100) NOT NULL DEFAULT '',
		full_dest VARCHAR(100) NOT NULL DEFAULT '',
		joinempty_dest VARCHAR(100) NOT NULL DEFAULT '',
		leaveempty_dest VARCHAR(100) NOT NULL DEFAULT '',
		joinunavail_dest VARCHAR(100) NOT NULL DEFAULT '',
		leaveunavail_dest VARCHAR(100) NOT NULL DEFAULT '',
		PRIMARY KEY (id)
	)";

$sql['vqplus_queue_config'] = "
	CREATE TABLE IF NOT EXISTS vqplus_queue_config (
		queue_num VARCHAR(20) NOT NULL default '',
		min_penalty INT DEFAULT NULL,
		max_penalty INT DEFAULT NULL,
		rule_id INT DEFAULT NULL,
		cdest VARCHAR(100) NOT NULL DEFAULT '',
		adest VARCHAR(100) NOT NULL DEFAULT '',
		full_dest VARCHAR(100) NOT NULL DEFAULT '',
		joinempty_dest VARCHAR(100) NOT NULL DEFAULT '',
		leaveempty_dest VARCHAR(100) NOT NULL DEFAULT '',
		joinunavail_dest VARCHAR(100) NOT NULL DEFAULT '',
		leaveunavail_dest VARCHAR(100) NOT NULL DEFAULT '',
		upil VARCHAR(6) NOT NULL DEFAULT 'no',
		lazymembers VARCHAR(6) NOT NULL DEFAULT 'no',
		PRIMARY KEY (queue_num)
	)";

$sql['vqplus_qrule_config'] = "
	CREATE TABLE IF NOT EXISTS vqplus_qrule_config (
		id INTEGER NOT NULL $autoincrement,
		name VARCHAR(40) NOT NULL DEFAULT '',
		PRIMARY KEY (id)
	)";

$sql['vqplus_qrule_detail'] = "
	CREATE TABLE IF NOT EXISTS vqplus_qrule_detail (
		rule_id INTEGER NOT NULL,
		elapsed INTEGER NOT NULL,
		min_penalty VARCHAR(10) NOT NULL DEFAULT '',
		max_penalty VARCHAR(10) NOT NULL DEFAULT '',
		PRIMARY KEY (rule_id, elapsed)
	)";

$sql['vqplus_callback_config'] = "
	CREATE TABLE IF NOT EXISTS vqplus_callback_config (
		id INTEGER NOT NULL $autoincrement,
		name VARCHAR(40) NOT NULL DEFAULT '',
		cid VARCHAR(100) DEFAULT NULL,
		numprepend VARCHAR(20) DEFAULT NULL,
		patterns TEXT,
		announcement VARCHAR(100) DEFAULT NULL, 
		recordname TINYINT(1) DEFAULT NULL, 
		promptreqnum VARCHAR(100) DEFAULT NULL, 
		promptreqname VARCHAR(100) DEFAULT NULL, 
		promptreqconfirm VARCHAR(100) DEFAULT NULL, 
		promptcb VARCHAR(100) DEFAULT NULL, 
		promptcbaccept VARCHAR(100) DEFAULT NULL, 
		cbqueue VARCHAR(20) DEFAULT NULL,
		timeout INTEGER,
		retries INTEGER,
		retrydelay INTEGER,
		maxcallbacks INTEGER,
		PRIMARY KEY (id)
	)";

$sql['vqplus_callback_calls'] = "
	CREATE TABLE IF NOT EXISTS vqplus_callback_calls (
		queue_num VARCHAR(20) NOT NULL,
		uniqueid VARCHAR(40) NOT NULL,
		cbid INTEGER NOT NULL,
		position INTEGER NOT NULL,
		callback VARCHAR(20) NOT NULL,
		PRIMARY KEY (uniqueid)
	)";

$sql['vqplus_callback_log'] = "
	CREATE TABLE IF NOT EXISTS vqplus_callback_log (
		timestamp INTEGER NOT NULL,
		queue_num VARCHAR(20) NOT NULL,
		uniqueid VARCHAR(40) NOT NULL,
		cbid INTEGER NOT NULL,
		callback VARCHAR(20) NOT NULL,
		success TINYINT(1) DEFAULT NULL,
		reason VARCHAR(80) NOT NULL,
		PRIMARY KEY (uniqueid)
	)";

foreach ($sql as $t => $s) {
	out(sprintf(_("creating table %s if needed"), $t));
	$result = $db->query($s);
	if(DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
}

outn(_("checking for upil field.."));
$sql = "SELECT `upil` FROM vqplus_queue_config";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
	$sql = "ALTER TABLE vqplus_queue_config ADD `upil` VARCHAR(6) NOT NULL DEFAULT 'no'";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		out(_("ERROR failed to update upil field"));
	} else {
		out(_("OK"));
	}
} else {
	out(_("already exists"));
}

outn(_("checking for lazymembers field.."));
$sql = "SELECT `lazymembers` FROM vqplus_queue_config";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
        // add new field
        $sql = "ALTER TABLE vqplus_queue_config ADD `lazymembers` VARCHAR(6) NOT NULL DEFAULT 'no'";
        $result = $db->query($sql);
        if(DB::IsError($result)) {
                out(_("ERROR failed to update lazymembers field"));
        } else {
                out(_("OK"));
        }
} else {
        out(_("already exists"));
}

outn(_("checking for promptcb field.."));
$sql = "SELECT `promptcb` FROM vqplus_callback_config";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
        // add new field
        $sql = "ALTER TABLE vqplus_callback_config "
	     . "DROP `prompt`, "
	     . "ADD `recordname` TINYINT(1) DEFAULT NULL, "
	     . "ADD `promptreqnum` VARCHAR(100) DEFAULT NULL, "
	     . "ADD `promptreqname` VARCHAR(100) DEFAULT NULL, "
	     . "ADD `promptreqconfirm` VARCHAR(100) DEFAULT NULL, "
	     . "ADD `promptcb` VARCHAR(100) DEFAULT NULL, "
	     . "ADD `promptcbaccept` VARCHAR(100) DEFAULT NULL";
        $result = $db->query($sql);
        if(DB::IsError($result)) {
                out(_("ERROR failed to update promptcb field"));
        } else {
                out(_("OK"));
        }
} else {
        out(_("already exists"));
}

outn(_("checking for retrydelay field.."));
$sql = "SELECT `retrydelay` FROM vqplus_callback_config";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
        // add new field
        $sql = "ALTER TABLE vqplus_callback_config "
	     . "ADD `retrydelay` INTEGER";
        $result = $db->query($sql);
        if(DB::IsError($result)) {
                out(_("ERROR failed to update retrydelay field"));
        } else {
                out(_("OK"));
        }
} else {
        out(_("already exists"));
}

outn(_("checking for announcement field.."));
$sql = "SELECT `announcement` FROM vqplus_callback_config";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
        // add new field
        $sql = "ALTER TABLE vqplus_callback_config "
	     . "ADD `announcement` VARCHAR(100) DEFAULT NULL";
        $result = $db->query($sql);
        if(DB::IsError($result)) {
                out(_("ERROR failed to update announcement field"));
        } else {
                out(_("OK"));
        }
} else {
        out(_("already exists"));
}

outn(_("checking for position field.."));
$sql = "SELECT `position` FROM vqplus_callback_calls";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
        // fix old fields
        $sql = "ALTER TABLE vqplus_callback_calls "
	     . "DROP `join_time`, "
	     . "CHANGE `exit_position` `position` INTEGER NOT NULL";
        $result = $db->query($sql);
        if(DB::IsError($result)) {
                out(_("ERROR failed to update position field"));
        } else {
                out(_("OK"));
        }
} else {
        out(_("already exists"));
}

file_put_contents('/var/spool/asterisk/sysadmin/restart_queuecallback', time());
