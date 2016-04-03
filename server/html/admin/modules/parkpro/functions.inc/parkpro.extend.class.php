<?php
// vim: set ai ts=4 sw=4 ft=php:
namespace FreePBX\modules\Parkpro;
class licenseCheck {
	private static $licensed = null;
	public static function check() {
		if(!isset(licenseCheck::$licensed)) {
			$keyname = "parkpro_exp"; // This is the key we're using in the licence file
			// Start off by assuming it's NOT licensed.
			licenseCheck::$licensed = false;

			if (zend_loader_file_encoded()) {  // If this file is obfuscated or licenced
				$lic_info = zend_loader_file_licensed(); // Return an array of modules in the licence file
				if (isset($lic_info[$keyname])) {
					$exp = new \DateTime($lic_info[$keyname]);
					if ($exp > new \DateTime("now")) {
						// DO SOMETHING HERE TO MARK IT AS LICENCED.
						licenseCheck::$licensed = true;
					}
				}
			} else { // If the current file ISN'T obfuscated/encoded, we consider it licenced.
				licenseCheck::$licensed = true;
			}
			return licenseCheck::$licensed;
		} else {
			return licenseCheck::$licensed;
		}
	}
}
