<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
/*
* Copyright 2011 by Schmooze Com., Inc.
* By installing, copying, downloading, distributing, inspecting or using the
* materials provided herewith, you agree to all of the terms of use as outlined
* in our End User Agreement which can be found and reviewed at
* http://www.schmoozecom.com/cmeula
*/

if(file_exists("/var/lib/asterisk/agi-bin/LoadLicenseIfExists.php")) {
    include_once("/var/lib/asterisk/agi-bin/LoadLicenseIfExists.php");
    include_once("functions.inc/functions_general.php");
}

function endpoint_module_repo_parameters_callback($opts) {
	global $amp_conf, $db;
	$final = array();

	$q = "SELECT brand, model, COUNT(*) as count FROM endpoint_extensions GROUP BY model";
	$res = $db->getAll($q, DB_FETCHMODE_ASSOC);
	if($db->IsError($res)){
		die_freepbx($res->getDebugInfo());
	}
	if (!empty($res)) {
		foreach($res as $key=>$value){
			$final['devices'][$value['brand']] = array(
				'model' => $value['model'],
				'count' => $value['count'],
			);
		}
	}

	return $final;
}

function endpoint_get_config($engine) {
    global $db;
	global $ext;
	global $core_conf;
	switch ($engine) {
		case 'asterisk':
			if (isset($core_conf) && is_a($core_conf, "core_conf") && (method_exists($core_conf, 'addSipNotify'))) {
				$core_conf->addSipNotify('aastra-check-cfg', array('Event' => 'check-sync', 'Content-Length' => '0'));
				$core_conf->addSipNotify('aastra-xml', array('Event' => 'aastra-xml', 'Content-Length' => '0'));
				$core_conf->addSipNotify('cisco-check-cfg', array('Event' => 'check-sync', 'Content-Length' => '0'));
				$core_conf->addSipNotify('cortelco-check-cfg', array('Event' => 'check-sync', 'Content-Length' => '0'));
				$core_conf->addSipNotify('digium-check-cfg', array('Event' => 'check-sync', 'Content-Length' => '0'));
				$core_conf->addSipNotify('grandstream-check-cfg', array('Event' => 'check-sync', 'Content-Length' => '0'));
				$core_conf->addSipNotify('linksys-cold-restart', array('Event' => 'reboot_now', 'Content-Length' => '0'));
				$core_conf->addSipNotify('linksys-warm-restart', array('Event' => 'restart_now', 'Content-Length' => '0'));
				$core_conf->addSipNotify('obihai-check-cfg', array('Event' => 'sync', 'Content-Length' => '0'));
				$core_conf->addSipNotify('polycom-check-cfg', array('Event' => 'check-sync', 'Content-Length' => '0'));
				$core_conf->addSipNotify('polycom-reboot', array('Event' => 'check-sync', 'Content-Length' => '0'));
				$core_conf->addSipNotify('sipura-check-cfg', array('Event' => 'resync', 'Content-Length' => '0'));
				$core_conf->addSipNotify('reboot-snom', array('Event' => 'reboot', 'Content-Length' => '0'));
				$core_conf->addSipNotify('spa-reboot', array('Event' => 'reboot', 'Content-Length' => '0'));
				$core_conf->addSipNotify('reboot-yealink', array('Event' => 'check-sync\;reboot=true', 'Content-Length' => '0'));
			}
			break;
	}
}


//set hooks
function endpoint_configpageinit($pagename) {
	 if(function_exists('endpoint_check_license')){
                $Endpoint = endpoint_check_license();
        } else {
                $Endpoint = false;
        }

	if ($Endpoint === true) {
		global $CC;
		$get_vars = array(
			'display'           => '',
			'extdisplay'        => '',
			'tech_hardware'     => '',
			'extension'         => '',
		);

		foreach ($get_vars as $k => $v) {
			$vars[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
		}
		//only hook in to extensions or users page when editing
		if (in_array($vars['display'], array('extensions', 'users')) && $vars['extdisplay'] || $vars['tech_hardware'] == 'sip_generic' || $vars['extension']){
			$CC->addprocessfunc('endpoint_core_load', 1);
			$CC->addprocessfunc('endpoint_core_process', 1);
		}
		if(isset($_SESSION['epmWrite'])){
			endpoint_write_ext($_SESSION['epmWrite']['ext'], $_SESSION['epmWrite']['brand']);
			unset($_SESSION['epmWrite']);
		}
	}
}

//hook in to extension/users page
//prapare page
function endpoint_core_load() {
	 if(function_exists('endpoint_check_license')){
                $Endpoint = endpoint_check_license();
        } else {
                $Endpoint = false;
        }
	
	if ($Endpoint === true) {
		global $CC, $display, $db;
		$get_vars = array('extdisplay' => '');
		foreach ($get_vars as $k => $v) {
			$vars[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
		}
		//get data together
		//build phone brand options
		$q = "SELECT * FROM endpoint_templates";
		$res = $db->getAll($q, DB_FETCHMODE_ASSOC);
		if($db->IsError($res)){
			die_freepbx($res->getDebugInfo());
		}
		foreach($res as $key=>$value){
			$brand[$value['brand']] = $value['brand'];
		}
		ksort($brand);
		foreach($brand as $value){
			if($value == 'and'){$value = 'AND';}
			$brandsUsed[] = array('text'=>ucfirst($value), 'value'=>ucfirst($value));
			$brandsUsed2[ucfirst($value)] = strtolower($value);
		}

		$brands = "{None: '', ";
		$brands_list = '';
		foreach($brandsUsed2 as $k=>$v){
			//build brand lists
			$brands .= "$k: '$k', ";
			$brands_list .= '<option value="' . $k . '">' . $k . '</option>';
			//build template lists
			$t = endpoint_TemplateOptions($v);
			$$v = $t[0];
			$v2 = $v . '_list';
			$$v2 = $t[1];
			//build model lists
			$t = endpoint_ModelOptions($v);
			$v2 = $v . 'Models';
			$$v2 = $t[0];
			$v2 = $v . 'Models_list';
			$$v2 = $t[1];
		}
		$brands .= "}";

		$tM = endpoint_get_all_models();
		foreach($tM as $k=>$v){
			$allModels[$v['model']] = $v['exp'];
		}
		echo '<script>';
			foreach($brandsUsed2 as $k=>$v){
				if(!empty($k)){
					if(!empty($$v)){
						echo 'var ' . $v . ' = ' . $$v . '; ';
					}
					$m = $v . 'Models';
					if(!empty($m)){
						echo 'var ' . $m . ' = ' . $$m . '; ';
					}
				}
			}
		echo '</script>';

		// get currents
		$sql = "SELECT * FROM endpoint_extensions WHERE `ext` = '" . $vars['extdisplay'] . "'";
		$current = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if($db->IsError($current)){
			die_freepbx($current->getDebugInfo());
		}
		if(!empty($current[0]['brand'])){
			//get templates/models for existing brand
			$templatesQ = "SELECT * FROM endpoint_templates WHERE `brand` = '" . strtolower($current[0]['brand']) . "' ORDER BY `template_name`";
			$templatesR = $db->getAll($templatesQ, DB_FETCHMODE_ASSOC);
			if($db->IsError($templatesR)){
				die_freepbx($templatesR->getDebugInfo());
			}
			foreach($templatesR as $k=>$v){
				$templates[] = array('text'=>$v['template_name'], 'value'=>$v['template_name']);
			}

			$modelsQ = "SELECT * FROM endpoint_models WHERE `brand` = '" . $current[0]['brand'] . "' ORDER BY `model`";
			$modelsR = $db->getAll($modelsQ, DB_FETCHMODE_ASSOC);
			if($db->IsError($modelsR)){
				die_freepbx($modelsR->getDebugInfo());
			}
			foreach($modelsR as $k=>$v){
				$models[] = array('text'=>$v['model'], 'value'=>$v['model'], 'accounts'=>$v['accounts']);
			}
			if(!empty($current[0]['model'])){
				foreach($models as $info){
					if($info['text'] == $current[0]['model']){
						$accts = $info['accounts'];
					}
				}
			}
			if(!empty($accts)){
				$i = 1;
				while($i <= $accts){
					$accounts[] = array('text'=>'Account ' . $i, 'value'=>'account' . $i);
					$i++;			
				}
			} else {
				$accounts[] = array('text'=>'Account 1', 'value'=>'account1');
				$accounts[] = array('text'=>'Account 2', 'value'=>'account2');
				$accounts[] = array('text'=>'Account 3', 'value'=>'account3');
				$accounts[] = array('text'=>'Account 4', 'value'=>'account4');
			}

			//set selected template and model
			if(!empty($current[0]['template'])){
				$templates[] = array('text'=>$current[0]['template'], 'value'=>$current[0]['template']);
				$template = $current[0]['template'];
			}
			if(!empty($current[0]['model'])){
				$models[] = array('text'=>$current[0]['model'], 'value'=>$current[0]['model']);
				$model = $current[0]['model'];
			}
			if(!empty($current[0]['account'])){
				$accounts[] = array('text'=>$current[0]['account'], 'value'=>$current[0]['account']);
				$account = $current[0]['account'];
			}
		} else {
			$model = ' ';
			$models[] = array('text'=>'Select Brand First', 'value'=>' ');
			$template = ' ';
			$templates[] = array('text'=>'Select Brand First', 'value'=>' ');
			$account = 'account1';
			$accounts[] = array('text'=>'Account 1', 'value'=>'account1');
		}

		//start layout
		$s = 'Endpoint';
		$CC->addguielem($s, new gui_hidden('ext_1', $vars['extdisplay']));
		$CC->addguielem($s, new gui_selectbox('brand_1', $brandsUsed, $current[0]['brand'], _('Brand'), _('Brand of device to be provisioned')));
		$CC->addguielem($s, new gui_textbox('mac_1', $current[0]['mac'], _('MAC'), _('MAC Address of device to be provisioned')));
		$CC->addguielem($s, new gui_selectbox('template_1', $templates, $template, _('Template'), _('Template to use for device')));
		$CC->addguielem($s, new gui_selectbox('brand_model_1', $models, $model, _('Model'), _('Model of device to be provisioned')));
		$CC->addguielem($s, new gui_selectbox('account_1', $accounts, $account, _('Account'), _('Account number to be assigned')));
		echo '<script type="text/javascript" src="modules/endpoint/assets/js/views/extensionsHook.js"></script>';
		
		
	}
}

function endpoint_core_process(){
	 if(function_exists('endpoint_check_license')){
                $Endpoint = endpoint_check_license();
        } else {
                $Endpoint = false;
        }

	if ($Endpoint === true) {
		$get_vars = array(
			'action'        => '',
			'extension'	=> '',
			'extdisplay'	=> '',
			'extension'     => '',
			);

		foreach ($get_vars as $k => $v) {
			$vars[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
		}
		if(!empty($vars['extension'])){
			$vars['extdisplay'] = $vars['extension'];
		}
		//do something with received data
		switch ($vars['action']) {
			case 'edit':
			case 'add':
				if(!empty($_POST['brand_model_1'])){
					endpoint_save_extensionHook($_POST);			
				}
				break;
			case 'del':
				endpoint_delete_extensionHook($vars);
				break;
			default:
				break;
		}
	}
}

function endpoint_module_install_check_callback($mods = array()) {
    global $active_modules;

    $ret = array();
    $current_mod = 'endpoint';
    $conflicting_mods = array('endpointman');

	foreach($mods as $k => $v) {
		if (in_array($k, $conflicting_mods) && !in_array($active_modules[$current_mod]['status'],array(MODULE_STATUS_NOTINSTALLED,MODULE_STATUS_BROKEN))) {
			$ret[] = $v['name'];
		}
	}

	if (!empty($ret)) {
		$modules = implode(',',$ret);
		return _('Failed to install ' . $modules . ' due to the following conflicting module(s): ' . $active_modules[$current_mod]['displayname']);
	}

	return TRUE;
}