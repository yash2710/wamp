<?php
$html = '';

$html .= heading('<span id=sendfaxtoggle style="cursor:pointer">' 
		. '<span id=faxhistorytoggle style="cursor:pointer">- </span>Stored Faxes', 2);
$html .= '<div id="line"><div class="spacer"></div><div class="spacer"></div></div>';
$html .= br();

if (isset($fax_set['maxpages'])) {
	$html .= '<div id=maxquota>';
	$html .= sprintf(_('You are using %s%% of your fax storage quota. '), $per);
	if ($per > 84) {
		$html .= '<span style="color:red">';
		switch ($max['maxaction']) {
			case 'reject':
				$html .= _('A full quota will cause new faxes to be rejected by the system.');
				break;
			case 'delete':
				$html .= _('A full quota will cause the system to delete stored fax '
						. 'to make room for the  ones.');
					break;
			default:
				break;
		}
		$html .= ' ' . _('Please ensure you have adequate space remaining.');
		$html .= '</span>';
	}
	$html .= '</div><br />';
}
//send fax
$table = new CI_Table;
$table->set_template(array('table_open' => '<table class="fax">'));
$table->set_heading(
				_('Fax'), 
				_('Date'), 
				_('From'), 
				_('To'), 
				_('Status'), 
				_('Pages'),
				_('View'),
				_('Forward'),
				_('Delete')
);

foreach ($fax as $f) {
	$fdir['class'] = $f['new'] == 'yes' ? 'new' : '';
	$date['class'] = $fdir['class'];
	$clid['class'] = $fdir['class'];
	$dest['class'] = $fdir['class'];
	$stat['class'] = $fdir['class'];
	$pags['class'] = $fdir['class'];
	$view['class'] = $fdir['class'];
	$fowd['class'] = $fdir['class'];
	$dete['class'] = $fdir['class'];
	
	$fdir['data'] = $f['dir'];
	$date['data'] = date('D, M. j, Y g:i a', $f['date']);
	$clid['data'] = $f['callid'];
	$dest['data'] = $f['dest'];
	$stat['data'] = $f['status'];
	$pags['data'] = $f['pages'];
	$view['data'] = $f['file_exists'] 
					? '<span class="pdfimg" data-fax-id="' . $f['faxid'] . '">pdf</span>'
					: '&nbsp';
	$fowd['data'] = $f['file_exists'] && $f['pages']
					? '<span class="forward" data-fax-id="' . $f['faxid'] . '"/>forward</span>' 
					: '&nbsp';
	$dete['data'] = '<span class="trashimg" data-fax-id="' . $f['faxid'] .'">delete</span>';

	$table->add_row(
		$fdir,
		$date,
		$clid,
		$dest,
		$stat,
		$pags,
		$view,
		$fowd,
		$dete
	);
}

$html .= $table->generate();

echo $html;
?>