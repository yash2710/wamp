<?php
foreach ($applications as $application) {
	$url = 'config.php?display=restapps&module=' . $application->name;

	$li[] = '<a href="' . $url . '"' .
		(($vars['module'] == $application->name) ? ' class="current ui-state-highlight"' : '') .
		'>' . $application->display . '</a>';
}

echo '<div class="rnav">' . ul($li) . '</div>';
?>
