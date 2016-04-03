<?php

class InstallerTest extends PHPUnit_Framework_TestCase {

	protected static $i;

	public static function setUpBeforeClass() {
		include 'setuptests.php';
		self::$i = FreePBX::Installer();
	}

	public function testFramework() {
		$wr = "/var/www/html";
		$i = self::$i;
		$checks = array (
			"amp_conf/htdocs/admin/ajax.php" => "$wr/admin/ajax.php",
			"amp_conf/astetc/cdr_mysql.conf" => "/etc/asterisk/cdr_mysql.conf",
			"amp_conf/sbin/amportal" => "/usr/local/sbin/amportal",
			"amp_conf/sounds/dir-intro-fnln.gsm" => "/var/lib/asterisk/sounds/dir-intro-fnln.gsm",
			"amp_conf/agi-bin/phpagi-asmanager.php" => "/var/lib/asterisk/agi-bin/phpagi-asmanager.php",
			"upgrades/2.9.0.md5" => false, // We don't install upgrade files.
			"amp_conf/bin/amportal" => false, // amportal isn't installed for the moment
		);

		foreach ($checks as $src => $dst) {
			$this->assertEquals($dst, $i->getDestination("framework", $src), "$src not pointing to $dst");
		}
	}

	public function testOtherModule() {
		$wr = "/var/www/html";
		$i = self::$i;
		$this->assertEquals($i->getDestination("cdr", "functions.inc.php"), "$wr/admin/modules/cdr/functions.inc.php", "default dest failed");
	}

}
