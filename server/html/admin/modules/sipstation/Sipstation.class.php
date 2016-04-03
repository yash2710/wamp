<?php
// vim: set ai ts=4 sw=4 ft=php:
namespace FreePBX\modules;
class Sipstation implements \BMO {

	public $ss = null;
	private $tollfree = "/(^888)|(^877)|(^866)|(^855)|(^844)|(^800)/";

	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new \Exception("Not given a FreePBX Object");
		}
		include(__DIR__.'/functions.inc/sipstation.inc.php');
		$this->ss = new \sipstation();
		$this->freepbx = $freepbx;
	}

	public function doConfigPageInit($page) {
	}

	public function install() {

	}
	public function uninstall() {

	}
	public function backup(){

	}
	public function restore($backup){

	}
	public function genConfig() {
	}

	/**
	 * Process the UCP Admin Page on submission
	 * @param {array} $user An array of submitted user data
	 */
	public function processUCPAdminDisplay($user) {
		if(!empty($_REQUEST['ucp|sipstation-sms-did']) && $this->smsEnabled() && $this->freepbx->Modules->checkStatus('sms')) {
			$this->freepbx->Sms->addUserRouting($user['id'],$_REQUEST['ucp|sipstation-sms-did']);
		} elseif($this->freepbx->Modules->checkStatus('sms')) {
			$this->freepbx->Sms->addUserRouting($user['id'],array());
		}
	}

	/**
	 * Get all Active DIDs for this Account
	 * @param {bool} $online       = true  Whether to force an online check
	 * @param {bool} $skiptollfree = false Whether to skip tollfree numbers
	 */
	public function getDIDs($online = true, $skiptollfree = false) {
		$key = $this->ss->get_key();
		if(!empty($key)) {
			$c = $this->ss->get_config($key, $online);
			if(!empty($c['dids'])) {
				if($skiptollfree) {
					$final = array();
					foreach($c['dids'] as $did) {
						if(!preg_match($this->tollfree,$did['did'])) {
							$final[] = $did;
						}
					}
				} else {
					$final = $c['dids'];
				}
				return $final;
			}
		}
		return array();
	}

	/**
	 * Get the UCP Display page
	 * @param {array} $user An Array containing user information
	 */
	public function getUCPAdminDisplay($user) {
		if($this->smsEnabled() && $this->freepbx->Modules->checkStatus('sms')) {
			$dids = $this->getDIDs(false,true);
			if(!empty($dids)) {
				$html['description'] = '<a href="#" class="info">'._("SIPStation SMS DIDs").':<span>'._("These are the SMS DIDs that are assigned to this user for use in UCP").'</span></a>';
				$html['content'] = load_view(dirname(__FILE__)."/views/ucp_config.php",array("dids" => $dids, "assigned" => $this->freepbx->Sms->getAssignedDIDs($user['id'])));
			}
		}
		return $html;
	}

	/**
	 * Send the adaptor if needed
	 */
	public function smsAdaptor() {
		include(__DIR__.'/functions.inc/SipstationSMS.class.php');
		return \FreePBX\modules\Sms\Adaptor\Sipstation::Create();
	}

	/**
	 * Check if SMS is enabled on the SIPStation Servers
	 */
	private function smsEnabled() {
		$key = $this->ss->get_key();
		if(!empty($key)) {
			$c = $this->ss->get_config($key, false);
			if(!empty($c['server_settings']['sms'])) {
				return true;
			}
		}
		return false;
	}
}
