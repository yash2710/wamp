<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

/* 	Generates dialplan for "sangoma mcu" components 
	We call this with retrieve_conf
*/
function sng_mcu_get_config($engine) {
	global $ext;  // is this the best way to pass this?
	global $amp_conf;
	global $version;

	switch($engine) {
		case "asterisk":
			$mcu_list = sng_mcu_get_details();
			
			/*
			Our Dialplan that transfers the call to the MCU
			*/
			if (!empty($mcu_list)) {
				foreach ($mcu_list as $mcu) {
					
					$mcu_details = sng_mcu_get_entries($mcu['id']);
					
					foreach ($mcu_details as $detail) {
						$c = 'sng-mcu-'.$mcu['id'];

						$ivr[$mcu['id']][] = $detail['conf'];
			
						if (!empty($detail['conf'])) {
							
							for ($i=1; $i<=3; $i++) {
								$conf_num = $detail['conf'] + $i;
							
								$host = sng_mcu_format_sip_url($mcu['host']);		
								$ext->add($c, $conf_num, '', new ext_setvar('TDIAL_STRING', 'SIP/' . $conf_num . '@' . $host));
								$ext->add($c, $conf_num, '', new ext_goto('tdial,1'));
							}
					
						}

						if (!empty($detail['ext'])) {
							$app_context = 'app-sng-mcu';
							$ext->add($app_context, $detail['ext'], '', new ext_goto('1', 's', 'mcu-' . $mcu['id'] . '-ivr-'. $detail['conf']));		
						}
					
					}	
				
					$ext->add($c, 'tdial', '', new ext_transfer('${TDIAL_STRING}'));
					$ext->add($c, 'tdial', 'hangit', new ext_hangup('')); 
				}
			
			}
		
			/*
			Dialplan for each conference room on an MCU
			*/
			foreach($ivr as $mcu_info  => $conf_info) {
				foreach ($conf_info as $key => $conf) {
					$announcement = sng_mcu_get_single_recording($mcu_info, $conf);
					$c = 'mcu-' . $mcu_info . '-ivr-' . $conf;
					$ext->add($c, 'fax', '', new ext_goto('${CUT(FAX_DEST,^,1)},${CUT(FAX_DEST,^,2)},${CUT(FAX_DEST,^,3)}'));
					$ext->add($c, 'h', '', new ext_hangup(''));
					$ext->add($c, 's', '', new ext_setvar('MSG', $announcement));
					$ext->add($c, 's', '', new ext_setvar('LOOPCOUNT', 0));
					$ext->add($c, 's', '', new ext_setvar('__DIR-CONTEXT', ''));
					$ext->add($c, 's', '', new ext_setvar('__IVR_CONTEXT_${CONTEXT}', '${IVR_CONTEXT}'));
					$ext->add($c, 's', '', new ext_setvar('__IVR_CONTEXT', '${CONTEXT}'));
					$ext->add($c, 's', '', new ext_gotoif('$["${CDR(disposition)}" = "ANSWERED"]', 'begin'));
					$ext->add($c, 's', '', new ext_answer(''));
					$ext->add($c, 's', '', new ext_wait('1'));	
					$ext->add($c, 's', 'begin', new ext_setvar('TIMEOUT(digit)', '3'));
					$ext->add($c, 's', 'begin', new ext_setvar('TIMEOUT(response)', '10'));
					$ext->add($c, 's', 'begin', new ext_setvar('__IVR_RETVM',''));
					$ext->add($c, 's', '', new ext_execif('$["${MSG}" != ""]', 'Background', '${MSG}'));
					$ext->add($c, 's', '', new ext_waitexten(''));
					$ext->add($c, 'hang', '', new ext_playback('vm-goodbye'));
					$ext->add($c, 'hang', '', new ext_hangup(''));
							
					for($i=1;$i<=3;$i++) {
						$ext->add($c, $i, '', new ext_noop('eleting: ${BLKVM_OVERRIDE} ${DB_DELETE(${BLKVM_OVERRIDE})}'));
						$ext->add($c, $i, '', new ext_setvar('__NODEST', ''));
						$ext->add($c, $i, '', new ext_goto('1', $conf + $i, 'sng-mcu-' . $mcu_info)); 
					}

					$ext->add($c, 'i', '', new ext_playback('invalid'));
					$ext->add($c, 'i', '', new ext_goto('1','loop'));
					$ext->add($c, 't', '', new ext_goto('1','loop'));	
                        		$ext->add($c, 'loop', '', new ext_setvar('LOOPCOUNT', '$[${LOOPCOUNT} + 1]'));
					$ext->add($c, 'loop', '', new ext_gotoif('$[${LOOPCOUNT} > 2]','hang'));
					$ext->add($c, 'loop', '', new ext_goto('1','begin'));	
                        	
					$ext->add($c, 'return', '', new ext_setvar('MSG', $announcement));
                	        	$ext->add($c, 'return', '', new ext_setvar('_IVR_CONTEXT','${CONTEXT}'));
					$ext->add($c, 'return', '', new ext_setvar('_IVR_CONTEXT_${CONTEXT}','${IVR_CONTEXT_${CONTEXT}}'));
					$ext->add($c, 'return', '', new ext_goto('s', 'begin'));
		
				}
				
				//Make Extensions Accessible to everyone on the system
				$ext->addInclude('from-internal-additional', $app_context);	
			}
			break;
	}
}
