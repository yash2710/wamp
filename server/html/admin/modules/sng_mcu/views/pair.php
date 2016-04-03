<?php
echo heading('Pair Sangoma MCU',3);
echo '<hr class="sng_mcu-hr"/>';

if (isset($error)) {
    echo $error . '<br><br>';
}

echo '<form method="post" action="' . $_SERVER['REQUEST_URI'] . '">';

$table = new CI_Table();
$table->add_row(fpbx_label('MCU URL', 'URL to MCU Device'), form_input('host', $host));
$table->add_row(fpbx_label('Requires API Token', 'Does this connection require the use of an API token?'), form_checkbox('auth',true,$auth, 'id="auth"'));
$table->add_row(array('data' => fpbx_label('API Token', 'Provide the API token that is configured in the Sangoma MCU Web Administration Interface'), 'class' => 'token'), array('data' => form_input('token',$token), 'class' => 'token'));

echo $table->generate();
?>
<input type="hidden" name="action" value="save_pair">
<input type="submit" />
</form>
