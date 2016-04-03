<script>
$('.extension-checkbox').change(function(event){
	var ext = $(this).data('extension');
	var name = $(this).data('name');
	if($(this).is(':checked')) {
		$('#ep-ext-list').append('<div class="ep-extensions" data-extension="'+ext+'"><label><input type="checkbox" name="ucp|endpoint[]" value="'+ext+'" checked> '+name+' &lt;'+ext+'&gt;</label><br /></div>');
	} else {
		$('.ep-extensions[data-extension="'+ext+'"]').remove();
	}
});
</script>
<div id="ep-ext-list" class="extensions-list">
<?php foreach($fpbxusers as $fpbxuser) {?>
	<div class="ep-extensions" data-extension="<?php echo $fpbxuser['ext']?>">
		<label>
			<input type="checkbox" name="ucp|endpoint[]" value="<?php echo $fpbxuser['ext']?>" <?php echo $fpbxuser['selected'] ? 'checked' : '' ?>> <?php echo $fpbxuser['data']['name']?> &lt;<?php echo $fpbxuser['ext']?>&gt;
		</label>
		<br />
	</div>
<?php } ?>
</div>
