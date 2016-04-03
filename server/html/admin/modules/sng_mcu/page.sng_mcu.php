<?php
$dir = dirname(__FILE__);

$get_vars = array(
                'action'        => '',
                'id'            => '',
                'display'       => '',
		'host'		=> '',
		'auth'  => '',
		'token'		=> ''
);

foreach ($get_vars as $k => $v) {
    $var[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}

if ($var['action'] !== 'del') {
	//Load navbar
	echo load_view($dir . '/views/rnav.php', array('sng_mcu_results' => sng_mcu_get_details()) + $var);
}

//here we want to catch error and redefine our action if we have any before saving 
switch($var['action']) {
	case 'save_pair':
		$url = sng_mcu_build_json_url($var['host']);
		if (isset($var['auth']) && $var['auth'] == true) {
			$var['api'] = sng_mcu_get_api_response($url, $var['token']);
		} else {
        		$var['api'] = sng_mcu_get_api_response($url);
		}
		if (!empty($_POST) && empty($var['api']) || isset($var['api']['error'])) {
			$var['action'] = 'add';
			$var['error'] = isset($var['api']['error'])?$var['api']['error'] : 'An error has occurred, please try again.';
		}
		break;
}

//Load pages
switch($var['action']) {
    case 'edit':
	$data = sng_mcu_get_details($var['id']);
        $var['host'] = $data[0]['host'];
        $var['auth'] = $data[0]['auth'];
        $var['token'] = $data[0]['token'];
        
	if (isset($var['auth']) && $var['auth'] == true) {
        	$var['api'] = sng_mcu_get_api_response(sng_mcu_build_json_url($var['host']), $var['token']);
        } else {
                $var['api'] = sng_mcu_get_api_response(sng_mcu_build_json_url($var['host']));
        }
	$var['details'] = sng_mcu_get_entries($var['id']);

	sng_mcu_put_sync($var['id'], date('Ymd'));

	echo load_view($dir . '/views/configure.php', $var);
	break;
    case 'update':
	$data = sng_mcu_get_details($var['id']);
        $var['host'] = $data[0]['host'];
        $var['auth'] = $data[0]['auth'];
        $var['token'] = $data[0]['token'];
	$var['sync'] = $data[0]['sync'];
 
	//check if the data has been changed
        $var['modified'] = sng_mcu_get_api_response(sng_mcu_build_json_url($var['host'], 'status'), $var['token']);
        $mod = str_replace('-',$var['modified']['data']['generate']['date']);
        if (isset($var['sync']) && $mod > $var['sync'] || !isset($var['sync'])) {
                //poll url to get updates and update the database
		if (isset($var['auth']) && $var['auth'] == true) {
                	$var['api'] = sng_mcu_get_api_response(sng_mcu_build_json_url($var['host']), $var['token']);
        	} else {
                	$var['api'] = sng_mcu_get_api_response(sng_mcu_build_json_url($var['host']));
        	}
		
                //Update our entries which means we need our entries first
		$entries = sng_mcu_get_entries($var['id']);
	
		sng_mcu_process_updated_entries($var['api'], $entries);	
        	
		sng_mcu_put_sync($var['id'], $mod);
	}
	unset($var['api']);
        $var['details'] = sng_mcu_get_entries($var['id']);

        echo load_view($dir . '/views/update.php', $var);
	break;
    case 'add':
	echo load_view($dir . '/views/pair.php', $var);
        break;	
    case 'save_pair':
        if (!empty($var['api'])) {
		$data = array(
        	                'host' => '',
        	                'id' => '',
				'auth' => '',
				'token' => ''
        	        );
        	$data = clean_post($data, $_POST);
		$data['id'] = isset($data['id'])?$data['id']:$var['id'];
		
		$var['id'] = sng_mcu_put_details($data);
	}
	echo load_view($dir . '/views/configure.php', $var);
	break;
    case 'save_config':
	$id = !empty($var['id'])?$var['id']:$_REQUEST['id'];
	$mcu = array();

	foreach ($_REQUEST as $key => $value) {
		foreach ($value as $k => $v) {
			switch ($key) {
				case 'conf_dial':
					$mcu[$id][$k]['ext'] = $v;
					break;
				case 'conf_name':
					$mcu[$id][$k]['name'] = $v;
					break;
				case 'announcement':
					$mcu[$id][$k]['announcement'] = $v;
					break;
				default:
					break;
			}
		}
	}
	
	if (!empty($id)) {
		sng_mcu_delete_entries($id);
	}

	sng_mcu_put_entries($mcu);

	needreload();
	break;
     case 'del':
	sng_mcu_delete_details($var['id']);
	sng_mcu_delete_entries($var['id']);	
     	//Load navbar
	echo load_view($dir . '/views/rnav.php', array('sng_mcu_results' => sng_mcu_get_details()) + $var);
	needreload();
     default:
	echo load_view($dir . '/views/landing.php', $var);
        break;
}
