<style>
.indent-div {
	margin-left: 15px;
}
.alert {
	width: 80%;
	padding: 15px;
	margin-bottom: 20px;
	border: 1px solid transparent;
	border-radius: 4px;
}
.alert-success {
	color: #468847;
	background-color: #dff0d8;
	border-color: #d6e9c6;
}
.alert-danger {
	color: #b94a48;
	background-color: #f2dede;
	border-color: #ebccd1;
}
</style>
<h2><?php echo _('Bria Cloud Solutions'); ?></h2>
<?php if(!empty($message)); {?>
	<div class="alert alert-<?php echo $message['type']; ?>"><?php echo $message['message']; ?></div>
<?php } ?>
<table>
	<tr>
		<td>
			<img id="ctl00_cphContent_vpMain_imgProduct" src="assets/bria/images/bria-cloud-products.png" alt="Bria Cloud Solutions" style="border-width:0px;">
		</td>
		<td>
			<p style="color: #f50; font-size: 16px; font-weight: bold;">
				<?php echo _('Turn Your Device Into a Unified Communications Portal'); ?>
			</p>
			<p>
				<?php echo _("CounterPath’s Bria Cloud Solutions combines Bria Stretto™ softphone clients for mobiles, tablets and desktops with CounterPath's Stretto Platform™ hosted Provisioning Module.  Stretto is a gateway, messaging, presence and provisioning platform that enables flexible solutions."); ?>
			</p>
			<p>
				<?php echo _("Capitalize on the integration between Bria & FreePBX to easily and efficiently deploy a cost effective system for procuring, distributing, provisioning and managing softphone clients. For a few dollars a month, you can give employees and remote workers the benefits of an easy-to-use communication solution-all centrally managed by the IT team."); ?>
			</p>
			<p>
				<?php echo _("Bria Cloud Solutions is available on desktop, mobile and tablet. Use these feature rich clients to turn your workspace into a dynamic, collaborative environment."); ?>
			</p>
			<p>
				<span style="font-weight: bold;"><?php echo _('Note'); ?>:</span> <?php echo _("In-order to successfully configure your Bria Cloud Solution, you must have access to your domain and domain admin email address."); ?>
			</p>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
		<?php
			$briaLinkText = '<a href="https://secure.counterpath.com/Store/CounterPath/BriaCloud.aspx" _target="Bria">'. _('Add Clients!') . '</a>';
			if (empty($username) || empty($password)) {
				$briaLink = '<a href="https://secure.counterpath.com/Store/CounterPath/BriaCloud.aspx" _target="Bria">'. _('Buy Now!') . '</a>';
			}
			echo $briaLinkText;
		?>
		</td>
	</tr>
</table>
<form autocomplete="off" name="edit" action="" method="post">
	<input type="hidden" name="action" value="save">
	<table>
		<tr>
			<td colspan="2">
				<div class="indent-div">
					<table>
							<tr class="guielToggle" data-toggle_class="BriaGeneral">
								<td colspan="2" >
									<h4>
										<span class="guielToggleBut">-  </span><?php echo _("General"); ?>
									</h4><hr>
								</td>
							</tr>
							<tr class="BriaGeneral">
								<td>
									<?php echo _('CounterPath Account Login'); ?>
									<span class="help">?
										<span style="display: none;">
											<?php echo _('CounterPath Account Login for your CounterPath Subscription Portal'); ?>
										</span>
									</span>
								</td>
								<td>
									<input type="text" id="bria1" name="username" value="<?php echo $username ?>" placeholder="Administrator Email">
								</td>
							</tr>
							<tr class="BriaGeneral">
								<td>
									<?php echo _('Provisioning Code'); ?>
									<span class="help">?
										<span style="display: none;">
											<?php echo _('Provisioning Code from the Systems tab within the CounterPath Subscription Portal.'); ?>
										</span>
									</span>
								</td>
								<td>
									<input type="password" id="bria2" name="password" value="<?php echo $password ?>" placeholder="Bria Cloud Provisioning Code">
								</td>
							</tr>
							<tr class="BriaGeneral">
								<td>
									<?php echo _('Extension Prefix'); ?>
									<span class="help">?
										<span style="display: none;">
											<?php echo _('A prefix to use when creating a Bria Cloud Solutions extension'); ?>
										</span>
									</span>
								</td>
								<td>
									<input type="text" id="bria3" name="prefix" value="<?php echo $prefix ?>" placeholder="The extension prefix for Bria Cloud Solutions extensions" class="extension">
								</td>
							</tr>
							<tr class="BriaGeneral">
								<td>
									<?php echo _('IP Address'); ?>
									<span class="help">?
										<span style="display: none;">
											<?php echo _('Public IP or Fully Qualified Domain Name used to connect to this PBX'); ?>
										</span>
									</span>
								</td>
								<td>
									<input type="text" id="bria4" name="ipaddr" value="<?php echo $ipaddr ?>" placeholder="IP of FQDN of the PBX">
								</td>
							</tr>
							<tr class="BriaGeneral">
								<td>
									<?php echo _('Login Information'); ?>
									<span class="help">?
										<span style="display:none;">
											<?php echo _('This is your Bria Cloud Solutions login suffix. Format: [username]@'.$groupname); ?>
										</span>
									</span>
								</td>
								<td>
									<?php echo '@'.$groupname; ?>
								</td>
							</tr>
							<tr class="BriaGeneral">
								<td>
									<?php echo _('API Connection'); ?>
									<span class="help">?
										<span style="display: none;">
											<?php echo _('Are you connected to the Bria Cloud Solutions Service?'); ?>
										</span>
									</span>
								</td>
								<td>
									<?php echo $connection; ?>
								</td>
							</tr>
					</table>
					<table>
							<tr class="guielToggle" data-toggle_class="BriaLimits">
								<td colspan="2" >
									<h4>
										<span class="guielToggleBut">-  </span><?php echo _("Limits"); ?>
									</h4><hr>
								</td>
							</tr>
							<tr class="BriaLimits">
								<td>
									<?php echo _('Users'); ?>
								</td>
								<td>
									<?php echo $limits['numUsers'] . ' / ' . $limits['maxUsers'] ?>
								</td>
							</tr>
							<tr class="BriaLimits">
								<td>
									<?php echo _('Devices'); ?>
								</td>
								<td>
									<?php echo $limits['deviceCount'] . ' / ' . $limits['deviceLimit'] ?>
								</td>
							</tr>
							<tr class="BriaLimits">
								<td>
									<?php echo _('Desktop'); ?>
								</td>
								<td>
									<?php echo $limits['desktopCount'] . ' / ' . $limits['desktopLimit'] ?>
								</td>
							</tr>
							<tr class="BriaLimits">
								<td>
									<?php echo _('Phone'); ?>
								</td>
								<td>
									<?php echo $limits['phoneCount'] . ' / ' . $limits['phoneLimit'] ?>
								</td>
							</tr>
							<tr class="BriaLimits">
								<td>
									<?php echo _('Tablet'); ?>
								</td>
								<td>
									<?php echo $limits['tabletCount'] . ' / ' . $limits['tabletLimit'] ?>
								</td>
							</tr>
					</table>
					<table>
							<tr class="guielToggle" data-toggle_class="BriaUsers">
								<td colspan="2" >
									<h4>
										<span class="guielToggleBut">-  </span><?php echo _("Users")?>
									</h4><hr>
								</td>
							</tr>
							<tr class="BriaUsers">
								<th>
									<?php echo _('Username'); ?>
								</th>
								<th>
									<?php echo _('User'); ?>
								</th>
								<th>
									<?php echo _('Extension'); ?>
								</th>
								<th>
									<?php echo _('Options'); ?>
								</th>
							</tr>
							<?php foreach ($users as $user) { ?>
							<tr class="BriaUsers">
								<td>
									<?php echo $user['username']; ?>
								</td>
								<td>
									<?php echo $user['fname'] . ' ' . $user['lname']; ?>
								</td>
								<td>
									<?php echo $user['default_extension']; ?>
								</td>
								<td>
									<a href="config.php?display=userman&action=showuser&user=<?php echo $user['id']; ?>"><?php echo _('Edit'); ?></a>
								</td>
							</tr>
							<?php } ?>
					</table>
				</div>
			</td>
		</tr>
	</table>
	<table>
		<tbody><tr>
			<td colspan="2"><input type="submit" name="submit" value="Submit"></td>
		</tr>
	</tbody></table>
</form>
