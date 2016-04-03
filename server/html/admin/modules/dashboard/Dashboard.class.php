<?php
// vim: set ai ts=4 sw=4 ft=php:
//
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2006-2014 Schmooze Com Inc.
//
// This module is primarily licenced under the AGPL version 3, or at your
// choice, any higher version.  However, as PHP SysInfo is licenced under
// the GPLv2, some parts of this module is dual licenced under GPLv2 -and-
// AGPL v3+. Those files have notations to signify this.
//

class Dashboard extends FreePBX_Helpers implements BMO {

	public function __construct($freepbx) {
		$this->db = $freepbx->Database;
	}

	// Always regen sys stats if they're older or equal to this, in seconds.
	private $maxage = 50;

	// If I've been polled, regen stats if it's been more than this
	// number of times the length of time it took to generate it (eg,
	// if it took .1 seconds, and this is set to 100, it'll regen the
	// status every 10 seconds (or if it took .2, every 20 seconds)
	// This is to avoid extra load on the server when it's not needed.
	private $regen = 100;

	// Keep this number of days worth of system stats
	private $history = 90;

	// IF you're adding a new builtin hook, add the descriptive name
	// and size and everything to classes/DashboardHooks, too.
	private $builtinhooks = array(
		"overview" => "getAjaxOverview",
		"blog" => "getBlogPosts",
		"notifications" => "getAjaxNotifications",
		"sysstat" => "getAjaxSysStat",
		"aststat" => "getAjaxAsteriskStat",
		"uptime" => "getAjaxUptime",
		"srvstat" => "getAjaxServerStats",
		"registered" => "getRegoInfo",

		// This is for testing, and isn't used. If you remove it, tests
		// will fail.
		"fake" => "fake",
	);

	// This is our scheduler task. It should run every minute.
	private $sched = "scheduler.php";

	public function install() {
	}
	public function uninstall() {
	}
	public function backup() {
	}
	public function restore($backup) {
	}
	public function runTests($db) {
		return true;
	}

	public function doConfigPageInit() {
	}

	public function myDialplanHooks() {
		return true;
	}

	public function doDialplanHook(&$ext, $engine, $priority) {
		// We're not actually doing any dialplan modifications. This
		// is just a handy place to discover modules that have requested hooks
		// into the status page.
		if (!class_exists('DashboardHooks')) {
			include 'classes/DashboardHooks.class.php';
		}
		$allhooks = DashboardHooks::genHooks($this->getConfig('visualorder'));
		$this->setConfig('allhooks', $allhooks);

		// Also, while we're here, we should check that our cronjob is
		// still there.

		$file = __DIR__."/".$this->sched;
		$cmd = "[ -e $file ] && $file";

		if (!$this->Cron->checkLine("* * * * * $cmd")) {
			// It's not!
			$this->Cron->addLine("* * * * * $cmd");
		}
	}

	public function ajaxRequest($req, &$setting) {
		return true;
	}

	public function ajaxHandler() {
		if (!class_exists('DashboardHooks')) {
			include 'classes/DashboardHooks.class.php';
		}

		switch ($_REQUEST['command']) {
			case "deletemessage":
				\FreePBX::create()->Notifications->safe_delete($_REQUEST['raw'], $_REQUEST['id']);
				return array("status" => true);
			break;
			case "resetmessage":
				\FreePBX::create()->Notifications->reset($_REQUEST['raw'], $_REQUEST['id']);
				return array("status" => true);
			break;
			case "saveorder":
				$this->setConfig('visualorder',$_REQUEST['order']);
				return array("status" => true);
			break;
			case "getcontent":
				if(file_exists(__DIR__.'/sections/'.$_REQUEST['rawname'].'.class.php')) {
					include(__DIR__.'/sections/'.$_REQUEST['rawname'].'.class.php');
					$class = '\\FreePBX\\modules\\Dashboard\\Sections\\'.$_REQUEST['rawname'];
					$class = new $class();
					return array("status" => true, "content" => $class->getContent($_REQUEST['section']));
				} else {
					return array("status" => false, "message" => "Missing Class Object!");
				}
			break;
			case "gethooks":
				if (!$this->getConfig('allhooks')) {
					$this->doDialplanHook($foo = null, null, null); // Avoid warnings.
				}
				// Remove next line to enable caching.
				// $this->doDialplanHook($foo = null, null, null); // Avoid warnings.
				$config = $this->getConfig('allhooks');
				$order = $this->getConfig('visualorder');
				if(!empty($order)) {
					foreach($config as &$page) {
						$entries = array();
						foreach($page['entries'] as $k => $e) {
							$o = isset($order[$e['section']]) ? $order[$e['section']] : $k;
							$entries[$o] = $e;
						}
						ksort($entries);
						$page['entries'] = $entries;
					}
				}
				return $config;
			break;
			case "sysstat":
				if (!class_exists('Statistics')) {
					include 'classes/Statistics.class.php';
				}
				$s = new Statistics();
				return $s->getStats();
			break;
			default:
				return DashboardHooks::runHook($_REQUEST['command']);
			break;
		}
	}

	public function runTrigger() {
		// This is run every minute.
		$this->getSysInfo();
	}

	public function genSysInfo() {

		// PHP SysInfo requires 'php-xml'. If it doesn't exist,
		// then we can't really do anything.
		if (!class_exists('DOMDocument')) {
			return false;
		}

		// Time how long it takes to run
		$start = microtime(true);
		if (!class_exists('SysInfo')) {
			include 'classes/SysInfo.class.php';
		}
		$si = SysInfo::create();
		$info = $si->getSysInfo();
		$end = microtime(true);
		$delay = $end - $start;
		// This is now a float in seconds of how long it took
		// to generate the sysinfo.

		$info['generationlength'] = $delay;

		$this->setConfig('latestsysinfo', $info);
		$this->setConfig($info['timestamp'], $info, 'MINUTES');

		$this->pruneSysInfo();

		return $info;
	}

	public function pruneSysInfo() {
		if (!class_exists('PruneHistory')) {
			include 'classes/PruneHistory.class.php';
		}

		$p = new PruneHistory();
		$p->doPrune();
	}

	public function getSysInfo() {
		$si = $this->getConfig('latestsysinfo');

		// Does it need to be updated? Older than $maxage seconds?
		if (!$si || $si['timestamp'] + $this->maxage <= time()) {
			$si = $this->genSysInfo();
			return $si;
		}

		// Is this older than $regen * generation time? (See header of this file)
		$genafter = ($si['generationlength'] * $this->regen) + $si['timestamp'];

		// If it is, regenerate. If not, keep using the cached data.
		if ($genafter < time()) {
			$si = $this->genSysInfo();
		}
		return $si;
	}

	public function getSysInfoPeriod($period = null) {
		// Return all of Period's SysInfo
		if ($period === null) {
			throw new Exception("No Period given");
		}
		$retarr = $this->getAll($period);
		return $retarr;
	}

	// Manage Built in Hooks
	public function doBuiltInHook($hookname) {
		$funcname = substr($hookname, 8);
		if (!isset($this->builtinhooks[$funcname]))
			throw new Exception("I was asked for $funcname, but I don't know what it is!");

		$methodname = $this->builtinhooks[$funcname];
		if (!method_exists($this, $methodname))
			throw new Exception("$funcname wants to use $methodname, but it doesn't exist");

		return $this->$methodname();
	}

	public function genStatusIcon($res, $tt = null) {
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

	// The actual hooks themselves!
	private function getAjaxOverview() {
		// Autoloaded!
		$o = new Overview();
		return $o->getHTML();
	}

	private function getAjaxAsteriskStat() {
		if (!class_exists('Statistics')) {
			include 'classes/Statistics.class.php';
		}
		$s = new Statistics();
		return $s->getHTML();
	}

	private function getAjaxSysStat() {
		if (!class_exists('SysStat')) {
			include 'classes/SysStat.class.php';
		}
		$sysstat = new SysStat();
		return $sysstat->getHTML();
	}

	private function getAjaxUptime() {
		if (!class_exists('Uptime')) {
			include 'classes/Uptime.class.php';
		}
		$tmp = new Uptime();
		return $tmp->getHTML();
	}

	private function getRegoInfo() {
		$html = "<p>There would be system info here</p><p>If rob had written it</p>\n";
		return $html;
	}

	private function getAjaxServerStats() {
		return "No\n";
	}
}
