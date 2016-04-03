<style>
.indent-div {
	margin-left: 15px;
}
</style>
<table>
	<tr class="guielToggle" data-toggle_class="Bria">
		<td colspan="2" ><h4><span class="guielToggleBut">-  </span><?php echo _("Bria Cloud Solutions"); ?></h4><hr></td>
	</tr>
	<tr>
		<td colspan="2">
			<div class="indent-div">
				<table>
					<tr class="Bria">
						<td>
							<?php echo _('Enabled'); ?>
							<span class="help">?
								<span style="display: none;">
									<?php echo _('Should this user have a Bria Cloud Solutions extension associated with it? Defaults to No'); ?>
								</span>
							</span>
						</td>
						<td>
							<span class="radioset">
								<input type="radio" id="bria1" name="bria|enable" value="true" <?php echo ($enabled) ? 'checked' : ''; ?>><label for="bria1"><?php echo _('Yes'); ?></label>
								<input type="radio" id="bria2" name="bria|enable" value="false" <?php echo (!$enabled) ? 'checked' : ''; ?>><label for="bria2"><?php echo _('No'); ?></label>
							</span>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>
<script>
var briaHandler = function( event ) {
	if($('input[name="password"]').val() == '******') {
		alert('<?php echo _('You enabled Bria Cloud Solutions but did not change your password')?>');
		$('input[name="password"]').focus();
		event.preventDefault();
		return false;
	}
};

$('#bria1').click(function() {
	var r=confirm('<?php echo _('Enabling Bria Cloud Solutions requires you to redefine your password and will expose your password in the database')?>');
	if(r==false){
		$('#bria2').click();
	} else {
		$('input[name="password"]').focus();
		$('form[name="editM"]').on('submit',briaHandler);
	}
})
$('#bria2').click(function() {
	$('form[name="editM"]').off('submit',briaHandler);
})
</script>
