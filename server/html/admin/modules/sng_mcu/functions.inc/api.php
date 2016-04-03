<?php

function sng_mcu_get_api_response($url, $apiKey = null) {
    $response = '';
    
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $headers = array(
	'Content-Type: application/json'
    );
    
    if ($apiKey !== null) {
        $headers[] = "X-API-KEY: $apiKey";
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    
    curl_close($ch);

    $ret = json_decode($response, TRUE);
    
    return $ret;
}

function sng_mcu_build_json_url($url, $type = 'config') {
    switch($type) {
	case 'status':
		$url_end = '/SAFe/sng_rest/api/status/application/configuration';
	break;
	case 'config':
	default:
		$url_end = '/SAFe/sng_rest/config.json';
	break;
    }
    if (strpos($url, ':')) {
	return $url . $url_end;
    } else {
    	return 'http://' . $url . ':81' . $url_end;
    }
}
