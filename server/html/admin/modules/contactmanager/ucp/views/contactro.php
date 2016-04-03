<div class="col-md-10">
	<div class="contact-container">
		<form role="form">
			<div class="form-group">
				<label>Display Name</label><br/>
				<?php echo $contact['displayname']?>
			</div>
			<div class="form-group">
				<label>First Name</label><br/>
				<?php echo $contact['fname']?>
			</div>
			<div class="form-group">
				<label>Last Name</label><br/>
				<?php echo $contact['lname']?>
			</div>
			<?php if(!empty($contact['title'])) {?>
				<div class="form-group">
					<label>Title</label><br/>
					<?php echo $contact['title']?>
				</div>
			<?php } ?>
			<?php if(!empty($contact['company'])) {?>
				<div class="form-group">
					<label>Company</label><br/>
					<?php echo $contact['company']?>
				</div>
			<?php } ?>
			<?php if(!empty($contact['numbers'])) {?>
				<div class="form-group">
					<label>Numbers</label><br/>
					<ul>
					<?php foreach($contact['numbers'] as $number) {?>
						<li data-flag='<?php echo json_encode($number['flags'])?>'><?php echo $number['type']?>: <span class="clickable" data-type="number" data-primary="<?php echo $number['primary']?>"><?php echo $number['number']?></span>
						<?php foreach($number['flags'] as $flag) {?>
							(<?php echo $flag?>)
						<?php } ?>
						</li>
					<?php } ?>
					</ul>
				</div>
			<?php } ?>
			<?php if(!empty($contact['xmpps'])) {?>
				<div class="form-group">
					<label>Xmpp</label><br/>
					<ul>
					<?php foreach($contact['xmpps'] as $number) {?>
						<li><span class="clickable" data-type="xmpp"><?php echo $number['xmpp']?></span></li>
					<?php } ?>
					</ul>
				</div>
			<?php } ?>
			<?php if(!empty($contact['emails'])) {?>
				<div class="form-group">
					<label>Emails</label><br/>
					<ul>
					<?php foreach($contact['emails'] as $number) {?>
						<li data-type="email"><a href="mailto:<?php echo $number['email']?>" target="_blank"><?php echo $number['email']?></a></li>
					<?php } ?>
					</ul>
				</div>
			<?php } ?>
			<?php if(!empty($contact['websites'])) {?>
				<div class="form-group">
					<label>Website</label><br/>
					<ul>
					<?php foreach($contact['websites'] as $number) {?>
						<li data-type="website"><a href="<?php echo preg_match("/^http/i",$number['website']) ? $number['website'] : "http://".$number['website']?>" target="_blank"><?php echo $number['website']?></a></li>
					<?php } ?>
					</ul>
				</div>
			<?php } ?>
		</form>
	</div>
</div>
