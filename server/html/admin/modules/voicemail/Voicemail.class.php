<?php
// vim: set ai ts=4 sw=4 ft=php:
namespace FreePBX\modules;
class Voicemail implements \BMO {
	//message to display to client
	public $displayMessage = array(
		"type" => "warning",
		"message" => ""
	);

	//supported playback formats
	public $supportedFormats = array(
		"oga" => "ogg",
		"wav" => "wav"
	);

	//supported greeting names
	public $greetings = array(
		'unavail' => 'Unavailable',
		'greet' => 'Name',
		'busy' => 'Busy',
		'temp' => 'Temporary',
	);

	//Voicemail folders to search
	private $folders = array(
		"INBOX",
		"Family",
		"Friends",
		"Old",
		"Work",
		"Urgent"
	);

	//limits the messages to process
	private $messageLimit = 3000;
	private $vmBoxData = array();
	private $vmFolders = array();
	private $vmPath = null;
	private $messageCache = array();
	public $Vmx = null;

	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new \Exception("Not given a FreePBX Object");
		}

		if(!class_exists('FreePBX\modules\Voicemail\Vmx') && file_exists(__DIR__.'/Vmx.class.php')) {
			include(__DIR__.'/Vmx.class.php');
			$this->Vmx = new Voicemail\Vmx($freepbx);
		}

		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
		$this->vmPath = $this->FreePBX->Config->get_conf_setting('ASTSPOOLDIR') . "/voicemail";
		$this->messageLimit = $this->FreePBX->Config->get_conf_setting('UCP_MESSAGE_LIMIT');
		foreach($this->folders as $folder) {
			$this->vmFolders[$folder] = array(
				"folder" => $folder,
				"name" => _($folder)
			);
		}
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

	public function processUCPAdminDisplay($user) {
		if(!empty($_POST['ucp|voicemail'])) {
			$this->FreePBX->Ucp->setSetting($user['username'],'Voicemail','assigned',$_POST['ucp|voicemail']);
		} else {
			$this->FreePBX->Ucp->setSetting($user['username'],'Voicemail','assigned',array());
		}
	}

	public function getUCPAdminDisplay($user) {
		$fpbxusers = array();
		$cul = array();
		foreach(core_users_list() as $list) {
			$cul[$list[0]] = array(
				"name" => $list[1],
				"vmcontext" => $list[2]
			);
		}
		$vmassigned = $this->FreePBX->Ucp->getSetting($user['username'],'Voicemail','assigned');
		$vmassigned = !empty($vmassigned) ? $vmassigned : array();
		foreach($user['assigned'] as $assigned) {
			$fpbxusers[] = array("ext" => $assigned, "data" => $cul[$assigned], "selected" => in_array($assigned,$vmassigned));
		}
		$html['description'] = '<a href="#" class="info">'._("Allowed Voicemail").':<span>'._("These are the assigned and active extensions which will show up for this user to control and edit in UCP").'</span></a>';
		$html['content'] = load_view(dirname(__FILE__)."/views/ucp_config.php",array("fpbxusers" => $fpbxusers));
		return $html;
	}

	public function getFolders() {
		return $this->vmFolders;
	}

	/**
	* Query the audio file and make sure it's actually audio
	* @param string $file The full file path to check
	*/
	public function queryAudio($file) {
		if(!file_exists($file) || !is_readable($file)) {
			return false;
		}
		$last = exec('sox '.$file.' -n stat 2>&1',$output,$ret);
		if(preg_match('/not sound/',$last)) {
			return false;
		}
		$data = array();
		foreach($output as $o) {
			$parts = explode(":",$o);
			$key = preg_replace("/\W/","",$parts[0]);
			$data[$key] = trim($parts[1]);
		}
		return $data;
	}

	public function deleteVMGreeting($ext,$type) {
		$o = $this->getVoicemailBoxByExtension($ext);
		$context = $o['vmcontext'];
		$vmfolder = $this->vmPath . '/'.$context.'/'.$ext;
		$file = $vmfolder."/".$type.".wav";
		if(isset($this->greetings[$type]) && file_exists($file)) {
			foreach(glob($vmfolder."/".$type.".*") as $filename) {
				if(!unlink($filename)) {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	public function copyVMGreeting($ext,$source,$target) {
		$o = $this->getVoicemailBoxByExtension($ext);
		$context = $o['vmcontext'];
		$vmfolder = $this->vmPath . '/'.$context.'/'.$ext;
		if(isset($this->greetings[$source]) && isset($this->greetings[$target])) {
			if(file_exists($vmfolder."/".$target.".wav")) {
				$this->deleteVMGreeting($ext,$target);
			}
			copy($vmfolder."/".$source.".wav",$vmfolder."/".$target.".wav");
			$this->generateAdditionalMediaFormats($vmfolder."/".$target.".wav",false);
		}
		return true;
	}

	public function saveVMGreeting($ext,$type,$format,$contents) {
		$o = $this->getVoicemailBoxByExtension($ext);
		$context = $o['vmcontext'];
		$vmfolder = $this->vmPath . '/'.$context.'/'.$ext;
		if(isset($this->greetings[$type])) {
			$file = $vmfolder."/".$type.".wav";
			$tempf = $vmfolder . "/" . $type . "_tmp.".$format;
			if(file_exists($file)) {
				if(!unlink($file)) {
					return false;
				}
			}
			file_put_contents($tempf,$contents);
			//convert the file here using sox I guess
			exec("sox " . $tempf . " -r 8000 -c1 " . $file . " > /dev/null 2>&1");
			unlink($tempf);
			$this->generateAdditionalMediaFormats($file,false);
			return true;
		} else {
			return false;
		}
	}

	public function getVoicemailBoxByExtension($ext) {
		if(empty($this->vmBoxData[$ext])) {
			include_once(__DIR__.'/functions.inc.php');
			$this->vmBoxData[$ext] = voicemail_mailbox_get($ext);
		}
		return !empty($this->vmBoxData[$ext]) ? $this->vmBoxData[$ext] : false;
	}

	public function getGreetingsByExtension($ext,$cache = false) {
		$o = $this->getVoicemailBoxByExtension($ext);
		//temp greeting <--overrides (temp.wav)
		//unaval (unavail.wav)
		//busy (busy.wav)
		//name (greet.wav)
		$context = $o['vmcontext'];
		$vmfolder = $this->vmPath . '/'.$context.'/'.$ext;
		$files = array();
		foreach(array_keys($this->greetings) as $greeting) {
			$file = $vmfolder . "/" . $greeting . ".wav";
			if($this->queryAudio($file)) {
				$files[$greeting] = $file;
				if(!$cache) {
					$this->generateAdditionalMediaFormats($file);
				}
			}
		}
		return $files;
	}

	public function saveVMSettingsByExtension($ext,$pwd,$email,$page,$playcid,$envelope) {
		$o = $this->getVoicemailBoxByExtension($ext);
		$context = $o['vmcontext'];
		$vmconf = voicemail_getVoicemail();
		if(!empty($vmconf[$context][$ext])) {
			$vmconf[$context][$ext]['pwd'] = $pwd;
			$vmconf[$context][$ext]['email'] = $email;
			$vmconf[$context][$ext]['pager'] = $page;
			$vmconf[$context][$ext]['options']['saycid'] = ($playcid) ? 'yes' : 'no';
			$vmconf[$context][$ext]['options']['envelope'] = ($envelope) ? 'yes' : 'no';
			voicemail_saveVoicemail($vmconf);
			return true;
		}
		return false;
	}

	public function deleteMessageByID($msg,$ext) {
		if(isset($this->greetings[$msg])) {
			return $this->deleteVMGreeting($ext,$msg);
		} else {
			$message = $this->getMessageByMessageIDExtension($msg,$ext);
			if(!empty($message)) {
				foreach(glob($message['path']."/".$message['fid'].".*") as $filename) {
					if(!unlink($filename)) {
						return false;
					}
				}
				foreach(glob($message['path']."/".$message['fid']."_*.*") as $filename) {
					if(!unlink($filename)) {
						return false;
					}
				}
				$this->renumberAllMessages($message['path']);
				return true;
			}
		}
		return false;
	}

	public function renumberAllMessages($folder) {
		$count = 0;
		foreach(glob($folder."/*.txt") as $filename) {
			preg_match('/msg([0-9]+).txt/',$filename,$matches);
			$msgnum = (int)$matches[1];
			if($msgnum != $count) {
				$newn = sprintf('%04d', $count);
				foreach(glob($folder."/msg".$matches[1].".*") as $filename2) {
					$newpath = preg_replace('/msg([0-9]+)/','msg'.$newn,$filename2);
					if(file_exists($newpath)) {
						unlink($newpath);
					}
					rename($filename2,$newpath);
				}
				foreach(glob($folder."/msg".$matches[1]."_*.*") as $filename2) {
					$newpath = preg_replace('/msg([0-9]+)/','msg'.$newn,$filename2);
					if(file_exists($newpath)) {
						unlink($newpath);
					}
					rename($filename2,$newpath);
				}
			}
			$count++;
		}
	}

	public function moveMessageByExtensionFolder($msg,$ext,$folder) {
		if(!$this->folderCheck($folder)) {
			return false;
		}
		$o = $this->getVoicemailBoxByExtension($ext);
		$context = $o['vmcontext'];
		$messages = $this->getMessagesByExtension($ext);
		$vmfolder = $this->vmPath . '/'.$context.'/'.$ext;
		$folder = $vmfolder."/".$folder;
		if(isset($messages['messages'][$msg])) {
			$info = $messages['messages'][$msg];
			$txt = $vmfolder."/".$info['folder']."/".$info['fid'].".txt";
			if(file_exists($txt) && is_readable($txt)) {
				$files = array();
				$files[] = $txt;
				$files[] = $vmfolder."/".$info['folder']."/" . $info['file'];
				if(!file_exists($folder)) {
					mkdir($folder);
				}
				if(is_writable($folder)) {
					foreach($info['format'] as $format) {
						if(file_exists($format['path']."/".$format['filename'])) {
							$files[] = $format['path']."/".$format['filename'];
						}
					}
					//check to make sure the file doesnt already exist first.

					//if the folder is empty (meaning we dont have a 0000 file) then set this to 0000
					$tname = preg_replace('/([0-9]+)/','0000',basename($txt));
					if(!file_exists($folder."/".$tname)) {
						foreach($files as $file) {
							$fname = preg_replace('/msg([0-9]+)/','msg0000',basename($file));
							rename($file, $folder."/".$fname);
						}
					} else {
						//Else we have other voicemail data in here so do something else

						//figure out the last file in the directory
						$oldFiles = glob($folder."/*.txt");
						$numbers = array();
						foreach($oldFiles as $file) {
							$file = basename($file);
							preg_match('/([0-9]+)/',$file,$matches);
							$numbers[] = $matches[1];
						}
						rsort($numbers);
						$next = sprintf('%04d', ($numbers[0] + 1));

						foreach($files as $file) {
							$fname = preg_replace('/msg([0-9]+)/',"msg".$next,basename($file));
							rename($file, $folder."/".$fname);
						}
					}
					//Just for sanity sakes recheck the directories hopefully this doesnt take hours though.
					$this->renumberAllMessages($vmfolder."/".$info['folder']);
					$this->renumberAllMessages($folder);
					return true;
				}
			}
		}
		return false;
	}

	public function getGreetingByExtension($greeting,$ext) {
		$greetings = $this->getGreetingsByExtension($ext,true);
		$o = $this->getVoicemailBoxByExtension($ext);
		$context = $o['vmcontext'];
		$data = array();
		if(isset($greetings[$greeting])) {
			$data['path'] = $this->vmPath . '/'.$context.'/'.$ext;
			$data['file'] = basename($greetings[$greeting]);
			foreach($this->supportedFormats as $format => $extension) {
				$mf = $data['path']."/".$greeting.".".$extension;
				if($this->queryAudio($mf)) {
					$data['format'][$format] = array(
						"filename" => basename($mf),
						"path" => $data['path'],
						"length" => filesize($mf)
					);
				}
			}
		}
		return $data;
	}

	public function getMessageByMessageIDExtension($msgid,$ext,$generateMedia = false) {
		if(isset($this->greetings[$msgid])) {
			$out = $this->getGreetingByExtension($msgid,$ext);
			return !empty($out) ? $out : false;
		} else {
			$messages = $this->getMessagesByExtension($ext);
			if(!empty($messages['messages'][$msgid])) {
				$msg = $messages['messages'][$msgid];
				if($generateMedia) {
					$this->generateAdditionalMediaFormats($msg['path']."/".$msg['file'],false);
				}
				return $messages['messages'][$msgid];
			} else {
				return false;
			}
		}
	}

	public function readMessageBinaryByMessageIDExtension($msgid,$ext,$format,$start=0,$buffer=8192) {
		$message = $this->getMessageByMessageIDExtension($msgid,$ext);
		$fpath = $message['format'][$format]['path']."/".$message['format'][$format]['filename'];
		if(!empty($message) && !empty($message['format'][$format]) && $this->queryAudio($fpath)) {
			$end = $message['format'][$format]['length'] - 1;
			$fp = fopen($fpath, "rb");
			fseek($fp, $start);
			if(!feof($fp) && ($p = ftell($fp)) <= $end) {
					if ($p + $buffer > $end) {
							$buffer = $end - $p + 1;
					}
				$contents = fread($fp, $buffer);
				fclose($fp);
				return $contents;
			}
			fclose($fp);
		}
		return false;
	}

	public function getMessagesByExtension($extension) {
		if(!empty($this->messageCache)) {
			return $this->messageCache;
		}
		$o = $this->getVoicemailBoxByExtension($extension);
		$context = $o['vmcontext'];

		$out = array();
		$vmfolder = $this->vmPath . '/'.$context.'/'.$extension;
		if (is_dir($vmfolder) && is_readable($vmfolder)) {
			$count = 1;
			foreach (glob($vmfolder . '/*',GLOB_ONLYDIR) as $folder) {
				foreach (glob($folder."/*.txt") as $filename) {
					//$start = microtime(true);
					if($count > ($this->messageLimit)) {
						$this->displayMessage['message'] = sprintf(_('Warning, You are over the max message display amount of %s only %s messages will be shown'),$this->messageLimit,$this->messageLimit);
						break 2;
					}
					$vm = pathinfo($filename,PATHINFO_FILENAME);
					$vfolder = dirname($filename);
					$txt = $vfolder."/".$vm.".txt";
					$wav = $vfolder."/".$vm.".wav";
					if(file_exists($txt) && is_readable($txt) && $this->queryAudio($wav)) {
						$data = $this->FreePBX->LoadConfig->getConfig($vm.".txt", $vfolder, 'message');
						$key = !empty($data['msg_id']) ? $data['msg_id'] : basename($folder)."_".$vm;
						$out['messages'][$key] = $data;
						$out['messages'][$key]['msg_id'] = $key;
						$out['messages'][$key]['file'] = basename($wav);
						$out['messages'][$key]['folder'] = basename($folder);
						$out['messages'][$key]['fid'] = $vm;
						$out['messages'][$key]['context'] = $context;
						$out['messages'][$key]['path'] = $folder;

						$out['messages'][$key]['format']['wav'] = array(
							"filename" => basename($wav),
							"path" => $folder,
							"length" => filesize($wav)
						);

						$sha = sha1_file($wav);
						foreach($this->supportedFormats as $format => $extension) {
							$mf = $vfolder."/".$vm."_".$sha.".".$extension;
							if($this->queryAudio($mf)) {
								$out['messages'][$key]['format'][$format] = array(
									"filename" => basename($mf),
									"path" => $folder,
									"length" => filesize($mf)
								);
							}
						}
						$out['total'] = $count++;
					}
				}
			}
		}
		$this->messageCache = $out;
		return $this->messageCache;
	}

	public function getMessagesByExtensionFolder($extension,$folder,$start,$limit) {
		$messages = $this->getMessagesByExtension($extension);
		$count = 1;
		$aMsgs = array();
		foreach($messages['messages'] as $message) {
			if($message['folder'] != $folder) {
				continue;
			}
			$id = $message['msg_id'];
			$aMsgs['messages'][$id] = $message;
			$count++;
		}
		if(empty($aMsgs)) {
			return $aMsgs;
		}
		$aMsgs['count'] = $count;

		usort($aMsgs['messages'], function($a, $b) {
			return $b['origtime'] - $a['origtime'];
		});
		$aMsgs['messages'] = array_values($aMsgs['messages']);

		$out = array();
		for($i=$start;$i<($start+$limit);$i++) {
			if(empty($aMsgs['messages'][$i])) {
				break;
			}
			$out['messages'][] = $aMsgs['messages'][$i];
		}
		return $out;
	}

	public function getMessagesCountByExtensionFolder($extension,$folder) {
		$messages = $this->getMessagesByExtension($extension);
		$count = 0;
		foreach($messages['messages'] as $message) {
			if($message['folder'] != $folder) {
				continue;
			}
			$count++;
		}
		return $count;
	}

	public function myDialplanHooks() {
		return true;
	}

	public function doDialplanHook(&$ext, $engine, $priority) {
		/* Determine if this is OK or not
			// We're not actually doing any dialplan modifications.
			foreach (glob($this->vmPath.'/device/*',GLOB_ONLYDIR) as $directory) {
				foreach($this->folders as $folder) {
					if(file_exists($directory.'/'.$folder)) {
						foreach (glob($directory.'/'.$folder.'/*.wav') as $filename) {
							if(preg_match('/msg(\d*)/',$filename,$matches)) {
								if(!file_exists($directory.'/'.$folder.'/msg'.$matches[1].'.ogg')) {
									$this->generateAdditionalMediaFormats($filename);
								}
							}
						}
					}
				}
			}
		*/
		}

	//TODO: Do this during retrieve_conf
	private function generateAdditionalMediaFormats($file,$background = true) {
		$b = ($background) ? '&' : ''; //this is so very important
		$path = dirname($file);
		$filename = pathinfo($file,PATHINFO_FILENAME);
		if(!$this->queryAudio($file)) {
			return false;
		}
		$sha1 = sha1_file($file);
		foreach($this->supportedFormats as $format) {
			switch($format) {
				case "ogg":
					if(!file_exists($path . "/" . $filename . "_".$sha1.".ogg")) {
						exec("sox $file " . $path . "/" . $filename . "_".$sha1.".ogg > /dev/null 2>&1 ".$b);
					}
				break;
			}
		}
		return true;
	}

	private function folderCheck($folder) {
		return !preg_match('/[\.|\/]/',$folder) && $this->validFolder($folder);
	}

	private function validFolder($folder) {
		return isset($this->vmFolders[$folder]);
	}
}
