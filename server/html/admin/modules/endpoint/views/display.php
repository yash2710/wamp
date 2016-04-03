<?php
//TODO: Inline javascript is very bad, this should be moved to Endpoint UCP Module's global.js file
echo '<script type="text/javascript" src="/admin/assets/endpoint/js/global.js"></script>';
echo '<script type="text/javascript" src="/admin/assets/endpoint/js/views/ucp.js"></script>';
echo '<script type="text/javascript" src="/admin/modules/endpoint/ucp/assets/js/admin.js"></script>';

if(!empty($view) && $view == 'Admin'){
	echo '<form id="' . $ext . '" name="' . $ext . '" class="saveAdminForm" method="post" action="?display=endpoint&view=extensions">';
} else {
	echo '<form id="' . $ext . '" name="' . $ext . '" method="post" action="?display=dashboard&mod=endpoint&sub=' . $ext . '">';
}

echo '<input type="hidden" name="template" value="' . $template . '">';
echo '<input type="hidden" name="brand" value="' . $brand . '">';
echo '<input type="hidden" name="ext" value="' . $ext . '">';
echo '<input type="hidden" name="model" value="' . $model . '">';

echo $modelKeys;
if(!empty($exp0Keys)){echo $exp0Keys;}
if(!empty($exp1Keys)){echo $exp1Keys;}
if(!empty($exp2Keys)){echo $exp2Keys;}
if(!empty($exp3Keys)){echo $exp3Keys;}
if(!empty($exp4Keys)){echo $exp4Keys;}
if(!empty($exp5Keys)){echo $exp5Keys;}

if(!empty($view) && $view == 'Admin'){
	echo '<input type="hidden" name="action" value="save_ucp_ext">'
		. '<button class="saveAdmin btn btn-default" data-type="save">Save Model</button>'
		. '<button class="saveAdmin btn btn-default" data-type="restart">and Restart</button>'
		. '<button class="saveAdmin btn btn-default" data-type="reset">Reset to Template</button>'
		. '</form><br /><br />';
} else {
	echo '<input type="hidden" name="action" value="save_ucp_ext">'
		. '<button class="saveTemplate btn btn-default" data-type="save">Save Model</button>'
		. '<button class="saveTemplate btn btn-default" data-type="restart">and Restart</button>'
		. '<button class="saveTemplate btn btn-default" data-type="reset">Reset to Template</button>'
		. '</form><br /><br />';
}
echo '</div>'; //end phoneInput
echo '</div>';



