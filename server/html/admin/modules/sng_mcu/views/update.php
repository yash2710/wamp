<?php

echo heading('Sangoma MCU Configuration for ' . $host ,3);

echo '<hr class="sng_mcu-hr"/>';

echo '<br ><br >
      <a href="/admin/config.php?display=sng_mcu&amp;id=' . $id . '&amp;action=del" id="del">
	<span>
		<img width="16" height="16" border="0" title="Delete MCU ' . $host . '" alt="" src="images/user_delete.png">&nbsp;Delete MCU ' . $host . '
	</span>
      </a>
      <br ><br >';

echo '<form method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="host" value="' . $host . '">';
echo '<input type="hidden" name="id" value="' . $id .'">';

$table = new CI_Table();

$table->set_heading('Configured', 'Conference Number', 'Dial Number', 'Name', 'Option Announcement');

global $currentcomponent;

$currentcomponent->addoptlistitem('recordings', '0', _('None'));
$currentcomponent->addoptlistitem('recordings', '-1', _('Default'));
foreach(recordings_list() as $r){
	$currentcomponent->addoptlistitem('recordings', $r['id'], $r['displayname']);
}
$currentcomponent->setoptlistopts('recordings', 'sort', false);

if (isset($details)) {
	foreach($details as $value) {
		if (function_exists('recordings_list')) {
                        $selAnnounce = isset($value['announcement'])?$value['announcement'] : '-1';
			$announcement = new gui_selectbox(
                                        'announcement[' . $value['conf'] . ']',
                                        $currentcomponent->getoptlist('recordings'),
                                        $selAnnounce,
                                        _('Announcement'),
                                        _('Greeting to be played on entry to the Ivr.'),
                                        false
                                );
                        $announcement_html = sng_mcu_isset($announcement->html_input);
		}
		$configured_input = (!empty($value['ext']))?'YES':'NO';
		$ext_input = array(
                        'name'  => 'conf_dial[' . $value['conf'] . ']',
                        'class' => 'extdisplay',
                        'value' => sng_mcu_isset($value['ext'])
                        );
                $name_input = array(
                        'name'  => 'conf_name[' . $value['conf'] . ']',
                        'value' => sng_mcu_isset($value['name'])
                        );

                $table->add_row(
                        $configured_input,
                        $value['conf'],
                        form_input($ext_input),
                        form_input($name_input),
                        $announcement_html
                        );
	}	

	$table->add_row('','','','','<input type="hidden" name="action" value="save_config">' . '<input type="submit" name="submit" value="Submit">');	
	echo $table->generate();
}

?>
</form>
<br />
<br />
<br />
