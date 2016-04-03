<div id="ucp-settings">
	<h3><?php echo _('User Control Panel Settings')?></h3>
	<div class="vmsettings">
		<div id="message" class="alert" style="display:none;"></div>
		<form role="form">
			<div class="form-group">
				<label for="displayname" class="help"><?php echo _('Display Name')?> <i class="fa fa-question-circle"></i></label>
				<input name="displayname" type="text" class="form-control" id="displayname" value="<?php echo $user['displayname']?>" autocapitalize="off" autocorrect="off" autocomplete="off">
				<span class="help-block help-hidden" data-for="displayname"><?php echo _('How you would like your name displayed throughout UCP and Contact Manager')?></span>
			</div>
			<div class="form-group">
				<label for="email" class="help"><?php echo _('Email')?> <i class="fa fa-question-circle"></i></label>
				<input name="email" type="text" class="form-control" id="email" value="<?php echo $user['email']?>" autocapitalize="off" autocorrect="off" autocomplete="off">
				<span class="help-block help-hidden" data-for="email"><?php echo _('Your Email Address')?></span>
			</div>
			<div class="form-group">
				<label for="pwd" class="help"><?php echo _('UCP Password')?> <i class="fa fa-question-circle"></i></label>
				<input name="pwd" type="password" class="form-control" id="pwd" value="******" autocapitalize="off" autocorrect="off" autocomplete="off">
				<span class="help-block help-hidden" data-for="pwd"><?php echo _('The password used to login to User Control Panel and other services')?></span>
			</div>
			<div class="form-group">
				<label for="fname" class="help"><?php echo _('First Name')?> <i class="fa fa-question-circle"></i></label>
				<input name="fname" type="text" class="form-control" id="fname" value="<?php echo $user['fname']?>" autocapitalize="off" autocorrect="off" autocomplete="off">
				<span class="help-block help-hidden" data-for="fname"><?php echo _('Your First Name')?></span>
			</div>
			<div class="form-group">
				<label for="lname" class="help"><?php echo _('Last Name')?> <i class="fa fa-question-circle"></i></label>
				<input name="lname" type="text" class="form-control" id="lname" value="<?php echo $user['lname']?>" autocapitalize="off" autocorrect="off" autocomplete="off">
				<span class="help-block help-hidden" data-for="lname"><?php echo _('Your Last Name')?></span>
			</div>
			<div class="form-group">
				<label for="title" class="help"><?php echo _('Title')?> <i class="fa fa-question-circle"></i></label>
				<input name="title" type="text" class="form-control" id="title" value="<?php echo $user['title']?>" autocapitalize="off" autocorrect="off" autocomplete="off">
				<span class="help-block help-hidden" data-for="title"><?php echo _('You Title')?></span>
			</div>
			<div class="form-group">
				<label for="company" class="help"><?php echo _('Company')?> <i class="fa fa-question-circle"></i></label>
				<input name="company" type="text" class="form-control" id="company" value="<?php echo $user['company']?>" autocapitalize="off" autocorrect="off" autocomplete="off">
				<span class="help-block help-hidden" data-for="company"><?php echo _('Your Company')?></span>
			</div>
			<div class="form-group">
				<label for="cell" class="help"><?php echo _('Cell Phone')?> <i class="fa fa-question-circle"></i></label>
				<input name="cell" type="text" class="form-control" id="cell" value="<?php echo $user['cell']?>" autocapitalize="off" autocorrect="off" autocomplete="off">
				<span class="help-block help-hidden" data-for="cell"><?php echo _('Your Cell Phone Number')?></span>
			</div>
			<div class="form-group">
				<label for="work" class="help"><?php echo _('Work Phone')?> <i class="fa fa-question-circle"></i></label>
				<input name="work" type="text" class="form-control" id="work" value="<?php echo $user['work']?>" autocapitalize="off" autocorrect="off" autocomplete="off">
				<span class="help-block help-hidden" data-for="work"><?php echo _('Your Work Number')?></span>
			</div>
			<div class="form-group">
				<label for="home" class="help"><?php echo _('Home Phone')?> <i class="fa fa-question-circle"></i></label>
				<input name="home" type="text" class="form-control" id="home" value="<?php echo $user['home']?>" autocapitalize="off" autocorrect="off" autocomplete="off">
				<span class="help-block help-hidden" data-for="home"><?php echo _('Your Home Number')?></span>
			</div>
			<div class="form-group">
				<label for="fax" class="help"><?php echo _('Fax')?> <i class="fa fa-question-circle"></i></label>
				<input name="fax" type="text" class="form-control" id="fax" value="<?php echo $user['fax']?>" autocapitalize="off" autocorrect="off" autocomplete="off">
				<span class="help-block help-hidden" data-for="fax"><?php echo _('Your Fax Number')?></span>
			</div>
			<?php if($desktop) {?>
				<div class="form-group desktopnotifications-group" style="display:none;">
					<label for="desktopnotifications-h" class="help"><?php echo _('Allow Desktop Notifications')?> <i class="fa fa-question-circle"></i></label>
					<div class="onoffswitch">
						<input type="checkbox" name="desktopnotifications" class="onoffswitch-checkbox" id="desktopnotifications">
						<label class="onoffswitch-label" for="desktopnotifications">
							<div class="onoffswitch-inner"></div>
							<div class="onoffswitch-switch"></div>
						</label>
					</div>
					<span class="help-block help-hidden" data-for="desktopnotifications-h"><?php echo _('Allow browser desktop notifications from UCP modules.')?></span>
				</div>
			<?php } ?>
		</form>
	</div>
</div>
