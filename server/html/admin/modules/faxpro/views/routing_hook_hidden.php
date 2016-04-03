<?php
$html = '';
$html .= '<tr>';
	$html .= '<td>';
	$html .= '</td><td>';
		$html .= form_hidden('t38gateway', $t38gateway);
	$html .= '</td>';
$html .= '</tr>';

$html = '<script type="text/javascript">var t38tr="' . rawurlencode($html) . '";</script>'
	. '<script type="text/javascript" src="modules/faxpro/assets/js/views/routing_hook.js"></script>';

echo $html;
