<?php if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}

function sng_mcu_format_sip_url($url) {
	
	$http = array( 'http://', 'https://');
	$url = str_replace($http, '', $url);
	$url = preg_split('/:/', $url);
	
	return $url[0];
}

function sng_mcu_get_details($id = '') { 
	global $db; 

	$sql = 'SELECT * FROM sng_mcu_details';
	
	if (!empty($id)) {
		$sql .= ' WHERE `id` = ' . $id; 
	}	
	
	$res = $db->getAll($sql, DB_FETCHMODE_ASSOC); 
	
	if($db->IsError($res)){ 
		die_freepbx($res->getDebugInfo()); 
	}  
	
	return $res; 
} 

function sng_mcu_put_details($sng_mcu) {
	global $db;
	
	$data = array(
		trim($sng_mcu['id']), 
		trim($sng_mcu['host']),
		(isset($sng_mcu['auth']) && $sng_mcu['auth'] === true)?true:false,
		trim($sng_mcu['token'])
	);
	
	$sql = $db->prepare('REPLACE INTO sng_mcu_details (`id`,`host`, `auth`, `token`) VALUES (?, ?, ?, ?)');	
	$ret = $db->execute($sql, $data);
	
	if($db->IsError($ret)) {
    		die_freepbx($ret->getDebugInfo()."\n".$ret->getUserInfo()."\n".$db->last_query);
  	}

	return $db->getOne( "SELECT LAST_INSERT_ID() FROM `sng_mcu_details`" );true;
}

function sng_mcu_get_entries($id = '') {
        global $db;
        
	$sql = 'SELECT * FROM sng_mcu_entries';	
	if (!empty($id)) {
                $sql .= ' WHERE `sng_mcu_id` = ' . $id;
        }
	
	$res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        
	if($db->IsError($res)){
                die_freepbx($res->getDebugInfo());
        }
        
	return $res;
}

function sng_mcu_put_entries($config) {
        global $db;
	$data = $set = array();
	
	foreach($config as $sng_mcu_id => $conf) {
		foreach ($conf as $conf_id => $settings) {
			$set['conf'] = $conf_id;
			$set['sng_mcu_id'] = $sng_mcu_id;
			foreach($settings as $key => $value) {
				$set[$key] = $value;	
			}
			$data[] = array(
				'sng_mcu_id' => $set['sng_mcu_id'], 
				'conf' => $set['conf'],
				'ext' => $set['ext'], 
				'name' => $set['name'],
				'announcement' => $set['announcement']
				);
		}
	}	
	
	$sql = $db->prepare('REPLACE INTO sng_mcu_entries (`sng_mcu_id`, `conf`, `ext`, `name`, `announcement`) VALUES (?, ?, ?, ?, ?)');
        $ret = $db->executeMultiple($sql, $data);

        if($db->IsError($ret)) {
                die_freepbx($ret->getDebugInfo()."\n".$ret->getUserInfo()."\n".$db->last_query);
        }

        return true;
}

function sng_mcu_put_sync($id,$date) {
	global $db;

	$sql = $db->prepare('UPDATE sng_mcu_details SET `sync` = ? WHERE `id` = ?');
        $ret = $db->execute($sql, array($date,$id));
	
	if($db->IsError($ret)) {
                die_freepbx($ret->getDebugInfo()."\n".$ret->getUserInfo()."\n".$db->last_query);
        }

        return true;
}

function sng_mcu_process_updated_entries($apiArray, $detailsArray) {
	$api = $apiArray['conference']['room'];
	foreach ($api as $key => $value) {
		$apiConf[] = $value['prefix'];
	}
	foreach ($detailsArray as $key => $value) {
		if (!isset($mcu_id)) {
			$mcu_id = $value['sng_mcu_id'];
		}
		$savedConf[] = $value['conf'];
	}

	foreach($savedConf as $value) {
		if (!in_array($value, $apiConf)) {
			//add
			$entry = array(
				'sng_mcu_id' => $mcu_id,
				'conf' => $value,
				'ext' => '',
				'name' => '',
				'announcement' => '-1'
			);
			sng_mcu_put_entries($entry);
		}
	}

	foreach($apiConf as $value) {
		if (!in_array($value, $savedConf)) {
			//delete
			sng_mcu_delete_entries_by_conf($mcu_id, $value);
		}
	}
	
}

function sng_mcu_delete_entries_by_conf($id, $conf) {
	global $db;
	
	$sql = $db->prepare('DELETE FROM sng_mcu_entries WHERE sng_mcu_id = ? AND conf = ?');
        $ret = $db->execute($sql, array($id,$conf));

        if ($db->IsError($ret)) {
                die_freepbx($ret->getDebugInfo()."\n".$ret->getUserInfo()."\n".$db->last_query);
        }
        return TRUE;
}

function sng_mcu_delete_entries($id) {
	global $db;
	
	$sql = $db->prepare('DELETE FROM sng_mcu_entries WHERE sng_mcu_id = ?');
	$ret = $db->execute($sql, $id);
	
	if ($db->IsError($ret)) {
		die_freepbx($ret->getDebugInfo()."\n".$ret->getUserInfo()."\n".$db->last_query);
	}
	return TRUE;
}

function sng_mcu_delete_details($id) {
        global $db;

        $sql = $db->prepare('DELETE FROM sng_mcu_details WHERE id = ?');
        $ret = $db->execute($sql, $id);

        if ($db->IsError($ret)) {
                die_freepbx($ret->getDebugInfo()."\n".$ret->getUserInfo()."\n".$db->last_query);
        }
        return TRUE;
}

function sng_mcu_get_single_recording($mcu_id, $conf_num) {
	global $db;
        $ret = '';
        $sql = 'SELECT `announcement` FROM sng_mcu_entries';
        if (!empty($mcu_id) && !empty($conf_num)) {
                $sql .= ' WHERE `sng_mcu_id` = ' . $mcu_id . ' AND `conf` = ' . $conf_num ;
        }

	$res = $db->getOne($sql);
        if($db->IsError($res)){
                die_freepbx($res->getDebugInfo());
        }
       
	switch($res) {
		case '0':
			$recFile = '';
			break;
		case '-1':
			$recFile = 'sng_mcu_chime';
			break;
		default:
			$recFile = recordings_get_file($res);
			break;
	} 
	return $recFile;
}

function sng_mcu_isset($value) {
	return (isset($value) && !empty($value))?$value:'';
}	
