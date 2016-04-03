<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
	$edit = $_GET['edit'];

	$enginename = false;
	$enginepath = false;

	// Handle adding/updating an engine
	if ($_POST['delete'])
	{
		ttsengines_delete_engine($_POST['engineid']);
	}
	else if ($_POST['edit'])
	{
		ttsengines_edit_engine($_POST['engineid'], $_POST['enginename'], $_POST['enginepath']);
	}
	else if ($_POST['addengine'])
	{
		ttsengines_add_engine($_POST['enginename'], $_POST['enginepath']);
	}

	$engines = ttsengines_get_all_engines();

?><h2><?php echo _('Text to Speech Engines')?></h2>

<!-- right side menu -->
<div class="rnav">
	<ul>
		<li><a href=?type=tool&display=ttsengines><?php echo _('Add Engine')?></a></li>
		<hr>
		<?php
			foreach($engines as $engine)
			{
				if ($edit && $engine['id'] == $edit)
				{
					$enginename = $engine['name'];
					$enginepath = $engine['path'];
				}
				echo '<li><a href=?type=tool&display=ttsengines&edit=' . $engine[id] . '>' . $engine[name] . '</a></li>';
			}
		?>
	<ul>
</div>

	<form action="" method="post">
		<p>
			<?php echo _('On this page you can manage text to speech engines on your system. When you add an engine you give it a name, and the full path to the engine on your system. After doing this the engine will be available on the text to speech page.');?>
 		</p>

		<?php 
			if ($edit)
			{
				echo '<input type="hidden" name="edit" value="1">';
				echo '<input type="hidden" name="engineid" value="' . $edit . '">';
			}
			else
			{
				echo '<input type="hidden" name="addengine" value="1">';
			}
		?>

		<p>
			<a href=# class="info"><?php echo _("Engine name")?><span><br><?php echo _("The name you enter will be shown in a drop down box on the Text to Speech page so you can select which engine you want to play your text.")?><br><br></span></a>: <input type=text name=enginename value=<?php echo $enginename ?>><br>

		</p>
	
		<p>
			<a href=# class="info"><?php echo _("Engine path")?><span><br><?php echo _("The full path to the binary of your text to speech engine. For example: /usr/sbin/magic_speech_engine.") ?><br><br></span></a>: <input type=text name=enginepath value=<?php echo $enginepath ?>><br>
		</p>
		
		<p>
			<input name=submit type=submit value="<?php echo _('Submit');?>"> <?php if ($edit) { echo "<input name=delete type=submit value="._('Delete').">"; } ?>
		</p>
	</form>
</div>
