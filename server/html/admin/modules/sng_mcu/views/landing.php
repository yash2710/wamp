<?php

if ((!$action && !$id) || $action == 'del') {
?>
    <h2><?php echo _("Sangoma MCU"); ?></h2>
<br/><br/>
<a href="config.php?type=setup&display=sng_mcu&action=add">
    <input type="button" value="Pair MCU" id="new_dir">
</a>

<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
<br/><br/><br/><br/><br/><br/><br/>

<?php
}
?>
