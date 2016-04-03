<?php
$vars = array(
	'module' => '',
	'action' => ''
);

foreach ($vars as $k => $v) {
	$vars[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}
$vars['ctrl'] = $ctrl;
$vars['applications'] = $ctrl->applications;
asort($vars['applications']);

//rnav here
echo load_view(dirname(__FILE__) . '/rnav.php', $vars);

if ($vars['module'] != '') {
	if (file_exists(dirname(__FILE__) . '/../modules/' . $vars['module'] . '/view.php')) {
		echo load_view(dirname(__FILE__) . '/../modules/' . $vars['module'] . '/view.php', $vars);
	} else {
?>
This module does not currently have any settings.
<?php
	}
} else {
?>
For information about this module, please visit the <a href="http://wiki.freepbx.org/display/FCM/RESTful+Phone+Apps">REST applications wiki page</a>.
<br/><br/>
<?php
}
?>
