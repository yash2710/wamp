<?php
/**
 * This is the User Control Panel Object.
 *
 * Copyright (C) 2013 Schmooze Com, INC
 * Copyright (C) 2013 Andrew Nagy <andrew.nagy@schmoozecom.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   FreePBX UCP BMO
 * @author   Andrew Nagy <andrew.nagy@schmoozecom.com>
 * @license   AGPL v3
 */
namespace UCP\Modules;
use \UCP\Modules as Modules;

class Donotdisturb extends Modules{
	protected $module = 'Donotdisturb';

	function __construct($Modules) {
		$this->Modules = $Modules;
	}

	public function getSettingsDisplay($ext) {
		$displayvars = array(
			"enabled" => $this->UCP->FreePBX->Donotdisturb->getStatusByExtension($ext)
		);
		$out = array(
			array(
				"title" => _('Do Not Disturb'),
				"content" => $this->load_view(__DIR__.'/views/settings.php',$displayvars),
				"size" => 6
			)
		);
		return $out;
	}

	/**
	 * Determine what commands are allowed
	 *
	 * Used by Ajax Class to determine what commands are allowed by this class
	 *
	 * @param string $command The command something is trying to perform
	 * @param string $settings The Settings being passed through $_POST or $_PUT
	 * @return bool True if pass
	 */
	function ajaxRequest($command, $settings) {
		if(!$this->_checkExtension($_POST['ext'])) {
			return false;
		}
		switch($command) {
			case 'enable':
				return true;
			default:
				return false;
			break;
		}
	}

	/**
	 * The Handler for all ajax events releated to this class
	 *
	 * Used by Ajax Class to process commands
	 *
	 * @return mixed Output if success, otherwise false will generate a 500 error serverside
	 */
	function ajaxHandler() {
		$return = array("status" => false, "message" => "");
		switch($_REQUEST['command']) {
			case 'enable':
				if($_POST['enable'] == 'true') {
					$this->UCP->FreePBX->Donotdisturb->setStatusByExtension($_POST['ext'],"YES");
				} else {
					$this->UCP->FreePBX->Donotdisturb->setStatusByExtension($_POST['ext']);
				}
				return array("status" => true, "alert" => "success", "message" => _('Do Not Disturb Has Been Updated!'));
				break;
			default:
				return $return;
			break;
		}
	}

	private function _checkExtension($extension) {
		$user = $this->UCP->User->getUser();
		return in_array($extension,$user['assigned']);
	}
}
