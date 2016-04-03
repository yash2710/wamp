#!/usr/bin/php -q

<?php
error_reporting(1);
//Include PEAR DB
include_once('DB.php');
if(!file_exists('/tftpboot/firmwaredownloads')){exec('/bin/mkdir /tftpboot/firmwaredownloads', $result);}
//Get our database information right from FreePBX and attempt to connect
$amp_conf = getconf((isset($_ENV['FREEPBXCONFIG']) && strlen($_ENV['FREEPBXCONFIG']))?$_ENV['FREEPBXCONFIG']:'/etc/amportal.conf');
if (!isset($db)) {
	$db = DB::connect('mysql://'.$amp_conf['AMPDBUSER'].':'.$amp_conf['AMPDBPASS'].'@'.$amp_conf['AMPDBHOST'].'/'.$amp_conf['AMPDBNAME']); // attempt connection
}

$ch = curl_init(); // Initialize Curl  
$url="http://ct.schmoozecom.net/epm.php"; //URL of the webpage you want to download  
curl_setopt($ch, CURLOPT_URL, $url); // Set CURL options  
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); //Return the handle if the curl session is set  
$output = curl_exec($ch); // execute the curl  
curl_close($ch); // close the curl 
$firmwares = json_decode($output, true);
ksort($firmwares);
//get versions from database
$getList = $db->getALL("SELECT * FROM endpoint_firmware");
if($db->IsError($server)) {
	die($server->getDebugInfo()."\n".$server->getUserInfo()."\n".$db->last_query);
}
foreach($getList as $firmware){
	if(!empty($firmware[1])){
		if($firmware[3] == "2" || $firmware[3] == NULL){
			endpoint_install_firmware($firmwares, $firmware, $version, '1');
		} else {
			echo "Slot 1 for " . $firmware[0] . " already installed\n\r";
		}
	} else {
		exec('/bin/rm -rf /tftpboot/' . $firmware[0] . '/1/', $result);
	}
	if(!empty($firmware[2])){
		if($firmware[4] == "2" || $firmware[4] == NULL){
			endpoint_install_firmware($firmwares, $firmware, $version, '2');
		} else {
			echo "Slot 2 for " . $firmware[0] . " already installed\n\r";
		}
	} else {
		exec('/bin/rm -rf /tftpboot/' . $firmware[0] . '/2/', $result);
	}
}
//update any that are still "installing" to failed as a fail safe.
$query = "UPDATE endpoint_firmware set `installed1` = '2' WHERE `installed1` = '3'";
$sql = $db->query($query);
$query = "UPDATE endpoint_firmware set `installed2` = '2' WHERE `installed2` = '3'";
$sql = $db->query($query);


echo "\n\r";

//removed defaults
/*
//make sure we have defaults in if in use.
echo "\r\n\r\nInstall default firmwares\r\n";
$usedTemplates = $db->getALL("select brand from endpoint_extensions;");
if($db->IsError($server)) {
	die($server->getDebugInfo()."\n".$server->getUserInfo()."\n".$db->last_query);
}
foreach($usedTemplates as $id=>$brands){
	$usedBrands[strtolower($brands[0])] = $brands[0];
}
foreach($firmwares as $brand=>$data){
    foreach($data as $id=>$info){
        if($info['recommended'] == 1 && !empty($usedBrands[lcfirst($brand)])){
            unset($ver);
            if(file_exists("/tftpboot/" . $brand . "/0/version")){
                $version = exec("/bin/grep '^Version.*' /tftpboot/" . $brand . "/0/version");
                $ver = substr($version, 8);
                echo $brand . ' Recommended slot version is ' . $ver . "\n\r";
                //echo $ver . "=" . $info['version'] . "\n\r";
            }
            if($ver != $info['version']){
                exec('wget http://ct.schmoozecom.net/' . $info['location'] . ' -O /tftpboot/firmwaredownloads/' . $info['location'], $result);
                exec('/bin/rm -rf /tftpboot/' . $brand . '/0/', $result);
                exec('/bin/mkdir /tftpboot/' . $brand, $result);
                exec('/bin/mkdir /tftpboot/' . $brand . '/0', $result);
                exec('tar -xzf /tftpboot/firmwaredownloads/' . $info['location'] . ' -C /tftpboot/' . $brand . '/0/', $result);
            }

        }
    }
}
*/
//add digium default
echo "\r\n\r\nInstall default Digium firmware\r\n";
foreach($firmwares as $brand=>$data){
    foreach($data as $id=>$info){
        if($info['recommended'] == 1 && $brand == 'digium'){
            unset($ver);
            if(file_exists("/tftpboot/digium/0/version")){
                $version = exec("/bin/grep '^Version.*' /tftpboot/digium/0/version");
                $ver = substr($version, 8);
                echo $brand . ' Recommended slot version is ' . $ver . "\n\r";
                //echo $ver . "=" . $info['version'] . "\n\r";
            }
            if($ver != $info['version']){
                exec('wget http://ct.schmoozecom.net/' . $info['location'] . ' -O /tftpboot/firmwaredownloads/' . $info['location'], $result);
                exec('/bin/rm -rf /tftpboot/digium/0/', $result);
                exec('/bin/mkdir /tftpboot/digium', $result);
                exec('/bin/mkdir /tftpboot/digium/0', $result);
                exec('tar -xzf /tftpboot/firmwaredownloads/' . $info['location'] . ' -C /tftpboot/digium/0/', $result);
            }
        }
    }
}

foreach($getList as $firmware){
    if($firmware[0] == 'bootrom'){
        unset($ver);
        //make sure we have polycom bootroms in.
        if(file_exists("/tftpboot/bootrom_version")){
            $version = exec("/bin/grep '^Version.*' /tftpboot/bootrom_version");
            $ver = substr($version, 8);
        }
        if($ver != $firmware[1]){
            exec('wget http://ct.schmoozecom.net/bootrom.' . $firmware[1] . '.tar.gz -O /tftpboot/firmwaredownloads/bootrom.tar.gz', $result);
            exec('/bin/rm -rf /tftpboot/*.bootrom.ld', $result);
            exec('/bin/rm -rf /tftpboot/bootrom.ld', $result);
            exec('/bin/rm -rf /tftpboot/version', $result);
            exec('tar -xzf /tftpboot/firmwaredownloads/bootrom.tar.gz -C /tftpboot/', $result);
        }
    }
}
function endpoint_install_firmware($firmwares, $firmware, $version, $slot){	
	include_once('DB.php');
	$amp_conf = getconf((isset($_ENV['FREEPBXCONFIG']) && strlen($_ENV['FREEPBXCONFIG']))?$_ENV['FREEPBXCONFIG']:'/etc/amportal.conf');
	if (!isset($db)) {
		$db = DB::connect('mysql://'.$amp_conf['AMPDBUSER'].':'.$amp_conf['AMPDBPASS'].'@'.$amp_conf['AMPDBHOST'].'/'.$amp_conf['AMPDBNAME']); // attempt connection
	}
	if(file_exists("/tftpboot/" . $firmware[0] . "/" . $slot . "/version")){
            $version = exec("/bin/grep '^Version.*' /tftpboot/" . $firmware[0] . "/" . $slot . "/version");
            $ver = substr($version, 8);
            echo 'Slot ' . $slot . ' version is ' . $ver . " -- database version is " . $firmware[1] . "\n\r";
        }
        if($ver != $firmware[$slot] || empty($firmware[$slot])){
		$query = "UPDATE endpoint_firmware set `installed" . $slot . "` = '3' WHERE `brand` = '" . $firmware[0] . "'";
		if($slot != '0'){$sql = $db->query($query);}
        foreach($firmwares[$firmware[0]] as $id=>$value){
            if($value['version'] == $firmware[$slot]){
                exec('wget http://ct.schmoozecom.net/' . $value['location'] . ' -O /tftpboot/firmwaredownloads/' . $value['location'], $result);
                exec('/bin/rm -rf /tftpboot/' . $firmware[0] . '/' . $slot . '/', $result);
				exec('/bin/mkdir /tftpboot/' . $firmware[0], $result);
				exec('/bin/mkdir /tftpboot/' . $firmware[0] . '/' . $slot . '', $result);
                exec('tar -xzf /tftpboot/firmwaredownloads/' . $value['location'] . ' -C /tftpboot/' . $firmware[0] . '/' . $slot . '/', $result);
            }
        }
//check for success and update db
        unset($ver);
        if(file_exists("/tftpboot/" . $firmware[0] . "/" . $slot . "/version")){
            $version = exec("/bin/grep '^Version.*' /tftpboot/" . $firmware[0] . "/" . $slot . "/version");
            $ver = substr($version, 8);
        }
        if($ver == $firmware[$slot]){
            echo "Firmware " . $slot . " Download Complete";
            $query = "UPDATE endpoint_firmware set `installed" . $slot . "` = '1' WHERE `brand` = '" . $firmware[0] . "'";
            if($slot != '0'){$sql = $db->query($query);}
        } else {
            echo "Firmware Failed";
            $query = "UPDATE endpoint_firmware set `installed" . $slot . "` = '2' WHERE `brand` = '" . $firmware[0] . "'";
            if($slot != '0'){$sql = $db->query($query);}
        }
    } else {
            $query = "UPDATE endpoint_firmware set `installed" . $slot . "` = '1' WHERE `brand` = '" . $firmware[0] . "'";
            if($slot != '0'){$sql = $db->query($query);}
	}
	if(empty($firmware[0])){
            $query = "UPDATE endpoint_firmware set `installed" . $slot . "` = '0' WHERE `brand` = '" . $firmware[0] . "'";
            if($slot != '0'){$sql = $db->query($query);}
	}
	
	echo "\r\n \r\n";
	
}

//Function to get our FreePBX Configuration Options
function getconf($filename) {
  $file = file($filename);
  foreach($file as $line => $cont){
  	if(substr($cont,0,1)!='#'){
  		$d=explode('=',$cont);
  		if(isset($d['0'])&& isset($d['1'])){$conf[trim($d['0'])]=trim($d['1']);}
  	}
  }
  return $conf;
}
