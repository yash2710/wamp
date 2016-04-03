<?php
$html = '';
$html .= '<tr>';
	$html .= '<td>';
		if (!$id) {
			$help = _(' Submit Changes needs to be pressed before this option '
				. 'can become available.');
		} else {
			$help = '';
		}
		$html .= fpbx_label(_('T38-Gateway Enabled'), 
			_('Set this to yes if you are using a t38 gateway device.') . $help);
	$html .= '</td><td>';
		$html .= '<span class="radioset">';
		$yes = form_label(_('Yes'), 't38yes');
		$yesdata = array(
				'name'	=> 't38gateway',
				'value'	=> 'yes',
				'id'	=> 't38yes'
		);
		$t38gateway == 'yes' ? $yesdata['checked'] = 'checked' : '';
		$id ? '' : $yesdata['disabled'] = 'disabled';

		$yesdata = form_radio($yesdata);


		$no = form_label(_('No'), 't38no');
		$nodata = array(
				'name'	=> 't38gateway',
				'value'	=> 'no',
				'id'	=> 't38no'
		);
		in_array($t38gateway, array('', 'no')) 
			? $nodata['checked'] = 'checked' : '';
		$t38gateway == 'no' ? $nodata['checked'] = 'checked' : '';
		$id ? '' : $nodata['disabled'] = 'disabled';
		
		$nodata = form_radio($nodata);
		
		$html .= $yes . $yesdata . $no . $nodata;
		$html .= '</span>';
	$html .= '</td>';
$html .= '</tr>';

$html = '<script type="text/javascript">var t38tr="' . rawurlencode($html) . '";</script>'
	. '<script type="text/javascript" src="modules/faxpro/assets/js/views/routing_hook.js"></script>';

echo $html;
