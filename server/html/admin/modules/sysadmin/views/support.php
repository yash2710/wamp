<?php echo heading(_("Support Files"), 3); ?>
<script type="text/javascript" src="modules/sysadmin/assets/js/views/support.js"></script>
<form>
<p>So stuff with support files goes here.</p>
<p>Random Session ID: <span id='sessid'></span></p>
<p>Check for upload from server: <span id='srvcheck'></span></p>
<p>Check for upload via browser: <span id='ajaxcheck'></span></p>
<?php
echo heading(_("Select items to upload"), 4);
$s = FreePBX::create()->Sysadmin;
print "<p>These look terrible. The bootstrap button code has been commented out. I don't know why.</p>";
echo "<div class='xbtn-group' data-toggle='buttons'>";
foreach($s->sections as $opts) {
	if ($opts['checked']) {
		$checked = " checked";
	} else {
		$checked = "";
	}
	print "<label class='xbtn xbtn-primary'><input type='checkbox' name='submit[]' value='".$opts['func']."' $checked>".$opts['name']."</label><br />\n";
}
?>
</div>
<script type='text/javascript'>$(document).ready(function(){ Support.init(); }); </script>

