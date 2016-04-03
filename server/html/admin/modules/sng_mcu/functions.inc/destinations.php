<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
 /* $Id: functions.inc.php 14238 2012-07-10 00:36:03Z p_lindheimer $ */

// The destinations this module provides
// returns a associative arrays with keys 'destination' and 'description'
function sng_mcu_destinations() {
        global $module_page;

        //get the list of IVR's
        $results = sng_mcu_get_entries();

        // return an associative array with destination and description
        if (isset($results)) {
                foreach($results as $result){
                        $mcu = sng_mcu_get_details($result['sng_mcu_id']);
			
			$name = $mcu[0]['host'] ? $mcu[0]['host'] . ' - '. $result['conf'] : 'MCU: ' . $result['id'] . ' Conf: ' . $result['conf'];
                        
			$extens[] = array(
				'destination' => 'mcu-' . $result['sng_mcu_id'] . '-ivr-' . $result['conf'] . ',' . $result['conf'] . ',1', 
				'description' => $name
				);
                
		}
        }

        if (isset($extens)) {
                return $extens;
        } else {
                return null;
        }
}

function sng_mcu_check_extensions($exten=true) {
        $extenlist = array();

        if (is_array($exten) && empty($exten)) {
                return $extenlist;
        }

	$results = sng_mcu_get_entries();

        foreach ($results as $result) {
                $thisexten = ($result['ext'] != '')?$result['ext']:'';
		
		$mcu = sng_mcu_get_details($results['sng_mcu_id']);
		
		if (!empty($thisexten)) {
                        $extenlist[$thisexten]['description'] = sprintf(_("Sangoma MCU Host: %s, Name: %s Ext: %s"), $mcu[0]['host'], $result['name'], $result['ext']);
                        $extenlist[$thisexten]['status'] = 'INUSE';
                        $extenlist[$thisexten]['edit_url'] = 'config.php?type=setup&display=sng_mcu&action=edit&id='.$result['sng_mcu_id'];
                }
        }
        return $extenlist;
}
