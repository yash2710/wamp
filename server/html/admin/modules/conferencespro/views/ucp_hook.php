<div id="conference-list" class="extensions-list">
<?php foreach($conferences as $conference) {?>
	<div class="conferences" data-conference="<?php echo $conference[0]?>">
		<label>
			<input type="checkbox" name="ucp|conferences[]" value="<?php echo $conference[0]?>" <?php echo in_array($conference[0],$selected) ? 'checked' : '' ?>> <?php echo $conference[1]?> &lt;<?php echo $conference[0]?>&gt;
		</label>
		<br />
	</div>
<?php } ?>
</div>
