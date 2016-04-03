<h2><?php echo _('General Settings')?></h2>
<?php if(!empty($message)) {?>
	<div class="alert alert-<?php echo $message['type']?>"><?php echo $message['message']?></div>
<?php } ?>
<form autocomplete="off" name="general" action="" method="post">
	<input type="hidden" name="type" value="general">
	<table>
		<tr class="guielToggle" data-toggle_class="userman">
			<td colspan="2"><h4><span class="guielToggleBut">-  </span><?php echo _("Email Settings")?></h4><hr></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo _("Email Subject")?>:<span><?php echo sprintf(_("Text to be used for the subject of the welcome email. Useable variables are:<ul><li>fname: First name</li><li>lname: Last name</li><li>brand: %s</li><li>title: title</li><li>username: Username</li><li>password: Password</li></ul><br>Empty the box to reset this field"),$brand)?></span></a></td>
				<td>
					<input type="text" name="emailsubject" size="80" value="<?php echo !empty($subject) ? $subject : _("Your %brand% Account")?>">
				</td>
			</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo _("Email Body")?>:<span><?php echo sprintf(_("Text to be used for the body of the welcome email. Useable variables are:<ul><li>fname: First name</li><li>lname: Last name</li><li>brand: %s</li><li>title: title</li><li>username: Username</li><li>password: Password</li></ul><br>Empty the box to reset this field"),$brand)?></span></a></td>
			<td>
				<textarea name="emailbody" rows="15" cols="80"><?php echo !empty($email) ? $email : file_get_contents(__DIR__.'/emails/welcome_text.tpl')?></textarea>
			</td>
		</tr>
	</table>
	<?php echo $hookHtml;?>
	<table>
		<tr>
			<td colspan="2"><input type="submit" name="submit" value="<?php echo _('Save')?>"></td>
			<td colspan="2"><input type="submit" name="sendemailtoall" value="<?php echo _('Save & resend email to all users')?>"></td>
		</tr>
	</table>
</form>
