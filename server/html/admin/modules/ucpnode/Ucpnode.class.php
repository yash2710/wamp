<?php
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2014 Schmooze Com Inc.
//
namespace FreePBX\modules;
class Ucpnode implements \BMO {
	private $nodever = "0.10.29";
	private $npmver = "1.3.6";
	private $icuver = "4.2.1";
	private $foreverroot = "/tmp";
	private $nodeloc = "/tmp";
	public function __construct($freepbx = null) {
		$this->db = $freepbx->Database;
		$this->freepbx = $freepbx;
		$this->foreverroot = $this->freepbx->Config->get('ASTVARLIBDIR') . "/ucp";
		$this->nodeloc = __DIR__."/node";
		if(!file_exists($this->nodeloc."/logs")) {
			mkdir($this->nodeloc."/logs");
		}
	}

	public function install() {
		$output = exec("node --version"); //v0.10.29
		$output = str_replace("v","",trim($output));
		if(empty($output)) {
			out("Node is not installed");
			return false;
		}
		if(version_compare($output,$nodever,"<")) {
			out("Node version is: ".$output." requirement is " . $nodever);
			return false;
		}


		$output = exec("npm --version"); //v0.10.29
		$output = trim($output);
		if(empty($output)) {
			out("Node Package Manager is not installed");
			return false;
		}
		if(version_compare($output,$npmver,"<")) {
			out("NPM version is: ".$output." requirement is " . $npmver);
			return false;
		}

		$output = exec("icu-config --version"); //v4.2.1
		$output = trim($output);
		if(empty($output)) {
			out("icu is not installed. You need to run: yum install icu libicu-devel");
			return false;
		}
		if(version_compare($output,$icuver,"<")) {
			out("ICU version is: ".$output." requirement is " . $icuver);
			return false;
		}



		$cwd = getcwd();
		chdir($this->nodeloc);
		putenv("PATH=/bin:/usr/bin");
		putenv("USER=".$this->freepbx->Config->get('AMPASTERISKUSER'));
		putenv("HOME=".sys_get_temp_dir());
		putenv("FOREVER_ROOT=".$this->foreverroot);
		putenv("SHELL=/bin/bash");
		outn("Installing/Updating Required Libraries. This may take a while...");
		$handle = popen("npm update 2>&1", "r");
		@unlink($this->nodeloc."/logs/install.log");
		$log = fopen($this->nodeloc."/logs/install.log", "a");
		while (($buffer = fgets($handle, 4096)) !== false) {
			//out(trim($buffer));
			fwrite($log,$buffer);
			outn(".");
		}
		fclose($log);
		out("");
		out("Finished updating libraries!");
		exec($this->nodeloc."/node_modules/forever/bin/forever restartall --plain 2>&1", $output, $ret);
		chdir($cwd);

		$set = array();
		$set['module'] = 'ucpnode';
		$set['category'] = 'UCP NodeJS Server';

		// HTTPENABLED
		$set['value'] = true;
		$set['defaultval'] =& $set['value'];
		$set['options'] = '';
		$set['name'] = 'Enable the NodeJS Server';
		$set['description'] = '';
		$set['emptyok'] = 0;
		$set['level'] = 1;
		$set['readonly'] = 0;
		$set['type'] = CONF_TYPE_BOOL;
		$this->freepbx->Config->define_conf_setting('NODEJSENABLED',$set);

		// HTTPBINDADDRESS
		$set['value'] = '0.0.0.0';
		$set['defaultval'] =& $set['value'];
		$set['options'] = '';
		$set['name'] = 'NodeJS Bind Address';
		$set['description'] = 'Address to bind to. Default is 0.0.0.0';
		$set['emptyok'] = 0;
		$set['type'] = CONF_TYPE_TEXT;
		$set['level'] = 2;
		$set['readonly'] = 0;
		$this->freepbx->Config->define_conf_setting('NODEJSBINDADDRESS',$set);

		// HTTPBINDPORT
		$set['value'] = '8001';
		$set['defaultval'] =& $set['value'];
		$set['options'] = '';
		$set['name'] = 'NodeJS Bind Port';
		$set['description'] = 'Port to bind to. Default is 8001';
		$set['emptyok'] = 0;
		$set['options'] = array(10,65536);
		$set['type'] = CONF_TYPE_INT;
		$set['level'] = 2;
		$set['readonly'] = 0;
		$this->freepbx->Config->define_conf_setting('NODEJSBINDPORT',$set);

		$this->freepbx->Config->commit_conf_settings();
	}
	public function uninstall() {
		putenv("PATH=/bin:/usr/bin");
		putenv("USER=".$this->freepbx->Config->get('AMPASTERISKUSER'));
		putenv("FOREVER_ROOT=".$this->foreverroot);
		exec($this->nodeloc."/node_modules/forever/bin/forever stopall --plain 2>&1", $output, $ret);
		exec("rm -Rf ".$this->nodeloc."/node_modules");
	}
	public function backup(){

	}
	public function restore($backup){

	}

	public function dashboardService() {
		$services = array(
			array(
				'title' => 'UCP Daemon',
				'type' => 'unknown',
				'tooltip' => _("Unknown"),
				'order' => 999,
				'command' => $this->nodeloc."/node_modules/forever/bin/forever list --plain"
			)
		);
		foreach($services as &$service) {
			$output = '';
			putenv("FOREVER_ROOT=".$this->foreverroot);
			exec($service['command']." 2>&1", $output, $ret);
			foreach($output as $line) {
				if(preg_match('/ucp/i',$line)) {
					preg_match('/ucp\.log(.*)/',$line,$matches);
					$uptime = preg_replace('/\x1B\[([0-9]{1,3}((;[0-9]{1,3})*)?)?[m|K]/','',trim($matches[1]));
					$service = array_merge($service, $this->genAlertGlyphicon('ok', _("Running (Uptime: " . $uptime . ")")));
					continue(2);
				}
			}

			if ($ret !== 0) {
				$service = array_merge($service, $this->genAlertGlyphicon('critical', _("Unable to launch monitor")));
				continue;
			} else {
				$service = array_merge($service, $this->genAlertGlyphicon('critical', _("UCP is not running")));
			}
		}

		return $services;
	}

	private function genAlertGlyphicon($res, $tt = null) {
		$glyphs = array(
			"ok" => "glyphicon-ok text-success",
			"warning" => "glyphicon-warning-sign text-warning",
			"error" => "glyphicon-remove text-danger",
			"unknown" => "glyphicon-question-sign text-info",
			"info" => "glyphicon-info-sign text-info",
			"critical" => "glyphicon-fire text-danger"
		);
		// Are we being asked for an alert we actually know about?
		if (!isset($glyphs[$res])) {
			return array('type' => 'unknown', "tooltip" => "Don't know what $res is", "glyph-class" => $glyphs['unknown']);
		}

		if ($tt === null) {
			// No Tooltip
			return array('type' => $res, "tooltip" => null, "glyph-class" => $glyphs[$res]);
		} else {
			// Generate a tooltip
			$html = '';
			if (is_array($tt)) {
				foreach ($tt as $line) {
					$html .= htmlentities($line, ENT_QUOTES)."\n";
				}
			} else {
				$html .= htmlentities($tt, ENT_QUOTES);
			}

			return array('type' => $res, "tooltip" => $html, "glyph-class" => $glyphs[$res]);
		}
		return '';
	}
}
