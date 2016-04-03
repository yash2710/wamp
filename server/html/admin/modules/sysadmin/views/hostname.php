<?php
echo heading('Hostname',3);
echo '<hr />';
echo '<form method="post" action="' . $_SERVER['REQUEST_URI'] . '" data-success-action="console.log(\"no reload here!\")">';
$table = new CI_Table();

//smart
$table->add_row(fpbx_label('Hostname:', _('The hostname of your pbx.')), '<input type="text" name="hostname" value="' . ((empty($hostname)) ?  '' : $hostname)
. '" placeholder="' . _('Enter your hostname') . '"/>');
//save
$table->add_row(fpbx_label('Update', _('If your hostname has changed recently and is not reflected on this page, hit the &quot;Save&quot; button below to update.')),
				'<input type="hidden" name="action" value="set_hostname">'
				. '<input type="submit" name="submit" value="Save/Update">');

echo $table->generate();
?>
</form>
<br />
<br />
<br />
