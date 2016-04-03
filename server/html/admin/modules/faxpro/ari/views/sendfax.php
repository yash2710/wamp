<?php
$html = '';

$html .= heading('<span id=sendfaxtoggle style="cursor:pointer">' 
		. ($faxhidesend ? '+' : '-') 
		. ' </span>Send a fax', 2);
		
$html .= '<div id="line"><div class="spacer"></div><div class="spacer"></div></div>';
$html .= br();

//send fax
if (!$faxhidesend) {
	$html .= '<div style="height:40px; position: fixed; left: 50%;">'
			. '<div class="notify" style="display: none;">&nbsp</div></div>';
	$html .= form_open_multipart($_SERVER['PHP_SELF'] 
			. '?' . $_SERVER['QUERY_STRING'], 'id="newfax"');
	$html .= form_hidden('MAX_FILE_SIZE', '10000000');
	$html .= form_hidden('f', 'action');
	$html .= form_hidden('m', 'faxpro');
	$html .= form_hidden('ajax', 'true');
	
	//cover page - receiver
	$table = new CI_Table;
	$table->set_template(
		array(
			'table_open' => '<table id="sendfaxarea" '
							. ($faxhidesend ? 'style="display: none;"' : '')
							. '>'
		)
	);
	$formats[]	= 'pdf';
	$formats[]	= 'tif';
	$label		= fpbx_label(_('File'), _('Select a file to upload. Supported formats are: ') . ul($formats));
	$delete		= '<img class="del_row" src="/admin/modules/faxpro/assets/images/delete.png" />';
	$table->add_row($label, form_upload('newfax[]') . $delete);
	$table->add_row('<img class="add_file" '
					. 'src="/admin/modules/faxpro/assets/images/add.png" '
					. 'title="' . _('attach another file') . '"/>');
	$table->add_row(_('Destination'), form_input('tonum'));
	$table->add_row(_('Coversheet?'), form_checkbox('coversheet'));
	$text['class'] = 'coveroptstr';
	$data['class'] = $text['class'];
	
	$text['data'] = _('Recipient Name');
	$data['data'] = form_input('name');
	$table->add_row($text, $data);
	
	$text['data'] = _('Message');
	$data['data'] = form_textarea(array(
						'name'	=> 'msg',
						'id'	=> 'tomsgbox',
						'rows'	=> 2
	));
	$cont['class'] = $text['class'];
	$cont['data']  = '<span id="tomsgcounter">1340</span>';
	$table->add_row($text, $data, $cont);
	
	//cover page - sender
	$text['data'] = _('My Details');
	$text['id'] = 'showmydeets';
	$text['rowspan'] = 2;
	$table->add_row($text);
	unset($text['id'], $text['rowspan']);
	
	$text['class'] = 'mycoveroptstr';
	$data['class'] = $text['class'];
	
	$text['data'] = _('My Name');
	$data['data'] = form_input('sender', $faxcovername);
	$table->add_row($text, $data);
	
	$text['data'] = _('My Tel');
	$data['data'] = form_input('tel', $faxcovertel);
	$table->add_row($text, $data);
	
	$text['data'] = _('My Email');
	$data['data'] = form_input('email', $faxcoveremail);
	$table->add_row($text, $data);
	
	$table->add_row(form_submit('send', _('Send Fax')));
	
	$html .= $table->generate();
	$table->clear();
	$html .= form_close() . br();
	
}

echo $html;
?>