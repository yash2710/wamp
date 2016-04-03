<?php /* $Id$ */
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

//set variables
$vars = array(
		'action'			=> null,
		'confirm_email'		=> '',
		'confirm_password'	=> '',
		'display'			=> '',
		'extdisplay'		=> null,
		'email_address'		=> '',
		'fw_popover' 		=> '',
		'fw_popover_process' => '',
		'logout'			=> false,
		'password'			=> '',
		'quietmode'			=> '',
		'restrictmods'		=> false,
		'skip'				=> 0,
		'skip_astman'		=> false,
		'type'				=> '',
		'username'			=> '',
		'unlock'			=> false,
);

foreach ($vars as $k => $v) {
		//were use config_vars instead of, say, vars, so as not to polute
		// page.<some_module>.php (which usually uses $var or $vars)
		$config_vars[$k] = $$k = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;

		//special handeling
		switch ($k) {
		case 'extdisplay':
				$extdisplay = (isset($extdisplay) && $extdisplay !== false)
						? htmlspecialchars($extdisplay, ENT_QUOTES)
						: false;
				$_REQUEST['extdisplay'] = $extdisplay;
				break;

		case 'restrictmods':
				$restrict_mods = $restrictmods
						? array_flip(explode('/', $restrictmods))
						: false;
				break;

		case 'skip_astman':
				$bootstrap_settings['skip_astman']	= $skip_astman;
				break;
		}
}

header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
header('Cache-Control: post-check=0, pre-check=0',false);
header('Pragma: no-cache');
header('Content-Type: text/html; charset=utf-8');

// This needs to be included BEFORE the session_start or we fail so
// we can't do it in bootstrap and thus we have to depend on the
// __FILE__ path here.
require_once(dirname(__FILE__) . '/libraries/ampuser.class.php');

session_set_cookie_params(60 * 60 * 24 * 30);//(re)set session cookie to 30 days
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);//(re)set session to 30 days
if (!isset($_SESSION)) {
		//start a session if we need one
		session_start();
}

//unset the ampuser if the user logged out
if ($logout == 'true') {
		unset($_SESSION['AMP_user']);
		exit();
}

//session_cache_limiter('public, no-store');
if (isset($_REQUEST['handler'])) {
		if ($restrict_mods === false) {
				$restrict_mods = true;
		}
		// I think reload is the only handler that requires astman, so skip it
		//for others
		switch ($_REQUEST['handler']) {
		case 'api':
				break;
		case 'reload';
				break;
		default:
				// If we didn't provide skip_astman in the $_REQUEST[] array it will be boolean false and for handlers, this should default
				// to true, if we did provide it, it will NOT be a boolean (it could be 0) so we will honor the setting
				//
				$bootstrap_settings['skip_astman'] = $bootstrap_settings['skip_astman'] === false ? true : $bootstrap_settings['skip_astman'];
				break;
		}
}

// call bootstrap.php through freepbx.conf
/*if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
		include_once('/etc/asterisk/freepbx.conf');
}*/

// At this point, we have a session, and BMO was created in bootstrap, so we can check to
// see if someone's trying to programatically log in.
if ($unlock) {
		if ($bmo->Unlock($unlock)) {
				unset($no_auth);
				$display = 'index';
		}
}

//redirect back to the modules page for upgrade
if(isset($_SESSION['modulesRedirect'])) {
	$display = 'modules';
	unset($_SESSION['modulesRedirect']);
}

// determine if the user has a session time out set in advanced settings. If the timeout is 0 or not set, we don't force logout
$sessionTimeOut = !empty($amp_conf['SESSION_TIMEOUT']) && is_numeric($amp_conf['SESSION_TIMEOUT']) ? $amp_conf['SESSION_TIMEOUT'] : false;
if ($sessionTimeOut !== false) {
	if (!empty($_SESSION['AMP_user']) && is_object($_SESSION['AMP_user'])) {
		//if we don't have last activity set it now
		if (empty($_SESSION['AMP_user']->_lastactivity)) {
			$_SESSION['AMP_user']->_lastactivity = time();
		} else {
			//check to see if we should be logged out or reset the last activity time
			if (($_SESSION['AMP_user']->_lastactivity + $sessionTimeOut) < time()) {
				unset($_SESSION['AMP_user']);
			} else {
				$_SESSION['AMP_user']->_lastactivity = time();
			}
		}
	}
}

/* If there is an action request then some sort of update is usually being done.
   This may protect from cross site request forgeries unless disabled.
 */
if (!isset($no_auth) && $action != '' && $amp_conf['CHECKREFERER']) {
		if (isset($_SERVER['HTTP_REFERER'])) {
				$referer = parse_url($_SERVER['HTTP_REFERER']);
				$refererok = (trim($referer['host']) == trim($_SERVER['SERVER_NAME']))
						? true : false;
		} else {
				$refererok = false;
		}
		if (!$refererok) {
				$display = 'badrefer';
		}
}
if (isset($no_auth) && empty($display)) {
		$display = 'noauth';
}
// handle special requests
if (!in_array($display, array('noauth', 'badrefer'))
		&& isset($_REQUEST['handler'])
) {
		$module = isset($_REQUEST['module'])	? $_REQUEST['module']	: '';
		$file 	= isset($_REQUEST['file'])		? $_REQUEST['file']		: '';
		fileRequestHandler($_REQUEST['handler'], $module, $file);
		exit();
}


if (!$quietmode) {
	$modulef =& module_functions::create();
	$modulef->run_notification_checks();
	$nt =& notifications::create();
	if ( !isset($_SERVER['HTACCESS']) && preg_match("/apache/i", $_SERVER['SERVER_SOFTWARE']) ) {
		// No .htaccess support
		if(!$nt->exists('framework', 'htaccess')) {
			$nt->add_security('framework', 'htaccess', _('.htaccess files are disable on this webserver. Please enable them'),
			sprintf(_("To protect the integrity of your server, you must allow overrides in your webserver's configuration file for the User Control Panel. For more information see: %s"), '<a href="http://wiki.freepbx.org/display/F2/Webserver+Overrides">http://wiki.freepbx.org/display/F2/Webserver+Overrides</a>'),"http://wiki.freepbx.org/display/F2/Webserver+Overrides");
		}
	} elseif(!preg_match("/apache/i", $_SERVER['SERVER_SOFTWARE'])) {
		$sql = "SELECT value FROM admin WHERE variable = 'htaccess'";
		$sth = FreePBX::Database()->prepare($sql);
		$sth->execute();
		$o = $sth->fetch();

		if(empty($o)) {
			if($nt->exists('framework', 'htaccess')) {
				$nt->delete('framework', 'htaccess');
			}
			$nt->add_warning('framework', 'htaccess', _('.htaccess files are not supported on this webserver.'),
			sprintf(_("htaccess files help protect the integrity of your server. Please make sure file paths and directories are locked down properly. For more information see: %s"), '<a href="http://wiki.freepbx.org/display/F2/Webserver+Overrides">http://wiki.freepbx.org/display/F2/Webserver+Overrides</a>'),"http://wiki.freepbx.org/display/F2/Webserver+Overrides",true,true);
			$sql = "REPLACE INTO admin (`value`, `variable`) VALUES (1, 'htaccess')";
			$sth = FreePBX::Database()->prepare($sql);
			$sth->execute();
		}
	} else {
		if($nt->exists('framework', 'htaccess')) {
			$nt->delete('framework', 'htaccess');
		}
	}
}

//draw up freepbx menu
$fpbx_menu = array();

// pointer to current item in $fpbx_menu, if applicable
$cur_menuitem = null;

// add module sections to $fpbx_menu
if(is_array($active_modules)){
		foreach($active_modules as $key => $module) {

				//create an array of module sections to display
				// stored as [items][$type][$category][$name] = $displayvalue
				if (isset($module['items']) && is_array($module['items'])) {
						// loop through the types
						foreach($module['items'] as $itemKey => $item) {

								// check access, unless module.xml defines all have access
								// BMO TODO: Per-module auth should be managed by BMO.
								//module is restricted to admin with excplicit permission
								$needs_perms = !isset($item['access'])
										|| strtolower($item['access']) != 'all'
										? true : false;

								//check if were logged in
								$admin_auth = isset($_SESSION["AMP_user"])
										&& is_object($_SESSION["AMP_user"]);

								//per admin access rules
								$has_perms = $admin_auth
										&& $_SESSION["AMP_user"]->checkSection($itemKey);

								//requies authentication
								$needs_auth = isset($item['requires_auth'])
										&& strtolower($item['requires_auth']) == 'false'
										? false
										: true;

								//skip this module if we dont have proper access
								//test: if we require authentication for this module
								//			and either the user isnt authenticated
								//			or the user is authenticated and dose require
								//				section specifc permissions but doesnt have them
								if ($needs_auth
										&& (!$admin_auth || ($needs_perms && !$has_perms))
								) {
										//clear display if they were trying to gain unautherized
										//access to $itemKey. If there logged in, but dont have
										//permissions to view this specicc page - show them a message
										//otherwise, show them the login page
										if($display == $itemKey){
												if ($admin_auth) {
														$display = 'noaccess';
												} else {
														$display = 'noauth';
												}
										}
										continue;
								}

								if (!isset($item['display'])) {
										$item['display'] = $itemKey;
								}

								// reference to the actual module
								$item['module'] =& $active_modules[$key];

								// item is an assoc array, with at least
								//array(module=> name=>, category=>, type=>, display=>)
								$fpbx_menu[$itemKey] = $item;

								// allow a module to replace our main index page
								if (($item['display'] == 'index') && ($display == '')) {
										$display = 'index';
								}

								// check current item
								if ($display == $item['display']) {
										// found current menuitem, make a reference to it
										$cur_menuitem =& $fpbx_menu[$itemKey];
								}
						}
				}
		}
}

//if display is modules then show the login page dont show does not exist as its confusing
if ($cur_menuitem === null && !in_array($display, array('noauth', 'badrefer','noaccess',''))) {
		if($display == 'modules') {
			$display = 'noauth';
			$_SESSION['modulesRedirect'] = 1;
		} else {
			$display = 'noaccess';
		}
}

// extensions vs device/users ... this is a bad design, but hey, it works
if (!$quietmode && isset($fpbx_menu["extensions"])) {
		if (isset($amp_conf["AMPEXTENSIONS"])
				&& ($amp_conf["AMPEXTENSIONS"] == "deviceanduser")) {
						unset($fpbx_menu["extensions"]);
				} else {
						unset($fpbx_menu["devices"]);
						unset($fpbx_menu["users"]);
				}
}

ob_start();
// Run all the pre-processing for the page that's been requested.
if (!empty($display) && $display != 'badrefer') {
		// $CC is used by guielemets as a Global.
		try {
			$CC = $currentcomponent = new component($display,$type);

			// BMO: Process ConfigPageInit functions
			$bmo->GuiHooks->doConfigPageInits($display, $currentcomponent);

			// now run each 'process' function and 'gui' function
			$currentcomponent->processconfigpage();
			$currentcomponent->buildconfigpage();
	} catch(Exception $e) {
		die_freepbx(_("FreePBX is Unable to Continue"), $e);
	}
}
$module_name = "";
$module_page = "";
$module_file = "";

// hack to have our default display handler show the "welcome" view
// Note: this probably isn't REALLY needed if there is no menu item for "Welcome"..
// but it doesn't really hurt, and it provides a handler in case some page links
// to "?display=index"
//TODO: acount for bad refer
if ($display == 'index' && ($cur_menuitem['module']['rawname'] == 'builtin')) {
		$display = '';
}

// show the appropriate page
switch($display) {
	case 'modules':
			// set these to avoid undefined variable warnings later
			//
			$module_name = 'modules';
			$module_page = $cur_menuitem['display'];
			include 'page.modules.php';
			break;
	case 'noaccess':
			show_view($amp_conf['VIEW_NOACCESS'], array('amp_conf' => &$amp_conf));
			break;
	case 'noauth':
			$config_vars['obe_error_msg'] = array();
			if ($config_vars['action'] == 'setup_admin'){
					$config_vars['obe_error_msg'] = framework_obe_intialize_validate(
							$config_vars['username'],
							$config_vars['password'],
							$config_vars['confirm_password'],
							$config_vars['email_address'],
							$config_vars['confirm_email']);
			}
			//if we have no admin users AND were trying to set one up
			if (!count(getAmpAdminUsers())
					&& $action == 'setup_admin'
					&& !$config_vars['obe_error_msg']
			) {
					//validate the inputs
					framework_obe_intialize_admin(
							$config_vars['username'],
							$config_vars['password'],
							$config_vars['confirm_password'],
							$config_vars['email_address'],
							$config_vars['confirm_email']
					);
			}

			//if we (still) have no admin users
			if (!count(getAmpAdminUsers())) {
					$login = $config_vars;
					$login['amp_conf'] = $amp_conf;
					$login['errors'] = $config_vars['obe_error_msg'];
					echo load_view($amp_conf['VIEW_OBE'], $login);
					unset($_SESSION['AMP_user']);
			}

			//prompt for a password if we have users
			if (count(getAmpAdminUsers())) {
					//error message
					$login['errors'] = array();
					if ($config_vars['username'] && $action !== 'setup_admin') {
							$login['errors'][] = _('Invalid Username or Password');
					}

					//show fop option if enabled, probobly doesnt belong on the
					//login page
					$login['panel'] = false;
					if (!empty($amp_conf['FOPWEBROOT'])
							&& is_dir($amp_conf['FOPWEBROOT'])
					){
							$login['panel'] = str_replace($amp_conf['AMPWEBROOT'] .'/admin/',
									'', $amp_conf['FOPWEBROOT']);
					}


					$login['amp_conf'] = $amp_conf;
					echo load_view($amp_conf['VIEW_LOGIN'], $login);
			}
			break;
	case 'badrefer':
			echo load_view($amp_conf['VIEW_BAD_REFFERER'], $amp_conf);
			break;
	case '':
			if ($astman) {
					show_view($amp_conf['VIEW_WELCOME'], array('AMP_CONF' => &$amp_conf));
			} else {
					// no manager, no connection to asterisk
					show_view($amp_conf['VIEW_WELCOME_NOMANAGER'],
							array('mgruser' => $amp_conf["AMPMGRUSER"]));
			}
			break;
	default:
			//display the appropriate module page
			$module_name = $cur_menuitem['module']['rawname'];
			$module_page = $cur_menuitem['display'];
			$module_file = 'modules/'.$module_name.'/page.'.$module_page.'.php';

			//TODO Determine which item is this module displaying.
			//Currently this is over the place, we should standardize on a
			//"itemid" request var for now, we'll just cover all possibilities :-(
			$possibilites = array(
					'userdisplay',
					'extdisplay',
					'id',
					'itemid',
					'selection'
			);
			$itemid = '';
			foreach($possibilites as $possibility) {
					if (isset($_REQUEST[$possibility]) && $_REQUEST[$possibility] != '' ) {
							$itemid = htmlspecialchars($_REQUEST[$possibility], ENT_QUOTES);
							$_REQUEST[$possibility] = $itemid;
					}
			}

			// create a module_hook object for this module's page
			try {
				$module_hook = moduleHook::create();

				// populate object variables
				$module_hook->install_hooks($module_page,$module_name,$itemid);

				// let hooking modules process the $_REQUEST
				$module_hook->process_hooks($itemid,
						$module_name,
						$module_page,
						$_REQUEST);

				// BMO: Pre display hooks.
				// getPreDisplay and getPostDisplay should probably never
				// be used.
				$bmo->GuiHooks->getPreDisplay($module_name, $_REQUEST);
			} catch(Exception $e) {
				die_freepbx(_("FreePBX is Unable to Continue"), $e);
			}

			// include the module page
			if (isset($cur_menuitem['disabled']) && $cur_menuitem['disabled']) {
					show_view($amp_conf['VIEW_MENUITEM_DISABLED'], $cur_menuitem);
					break; // we break here to avoid the generateconfigpage() below
			} else if (file_exists($module_file)) {
					//check module first and foremost, but not during quietmode
					if(!isset($_REQUEST['quietmode']) && $amp_conf['SIGNATURECHECK'] && (!isset($_REQUEST['action']) || $_REQUEST['action'] != 'popup')) {
						//Since we are viewing this module update it's signature
						try {
							$gpgstatus = module_functions::create()->updateSignature($module_name,false);
							//check all cached signatures
							$modules = module_functions::create()->getAllSignatures();
						} catch(Exception $e) {
							die_freepbx(_("FreePBX is Unable to Continue"), $e);
						}
						if(!$modules['validation']) {
							$type = (!empty($modules['statuses']['untrusted']) || !empty($modules['statuses']['tampered'])) ? 'danger' : 'warning';
							$merged = array();
							//priority sorting
							$stauses = array("revoked","untrusted","tampered","unsigned","unknown");
							foreach($stauses as $st) {
								if(!empty($modules['statuses'][$st])) {
									$merged = array_merge($merged,$modules['statuses'][$st]);
								}
							}
							$d = FreePBX::notifications()->list_security(true);
							foreach($d as $n) {
								//Dont show the same notifications twice
								if(!in_array($n['id'],array('FW_REVOKED','FW_UNSIGNED','FW_UNTRUSTED','FW_TAMPERED','FW_UNKNOWN'))) {
									array_unshift($merged,$n['display_text']);
								}
							}
							switch($type) {
								case 'danger':
									echo generate_message_banner(_('Security Warning'), 'danger',$merged,'http://wiki.freepbx.org/display/F2/Module+Signing',true);
								break;
								case 'warning':
									echo generate_message_banner(_('Security Warning'), 'warning',$merged,'http://wiki.freepbx.org/display/F2/Module+Signing', true);
								break;
							}
						}
					}

					if(isset($gpgstatus['status']) && ($gpgstatus['status'] & GPG::STATE_REVOKED)) {
						echo sprintf(_("File %s has a revoked signature. Can not load"),$module_file);
					} else {
						// load language info if available
						modgettext::textdomain($module_name);
						try {
							if ($bmo->GuiHooks->needsIntercept($module_name, $module_file)) {
								$bmo->GuiHooks->doIntercept($module_name, $module_file);
							} else {
								include($module_file);
							}
						} catch(Exception $e) {
							die_freepbx(_("FreePBX is Unable to Continue"), $e);
						}
					}
			} else {
					echo sprintf(_("404 Not found (%s)"),$module_file);
			}

			// BMO TODO: Post display hooks.
			try {
				$bmo->GuiHooks->getPostDisplay($module_name, $_REQUEST);
			} catch(Exception $e) {
				die_freepbx(_("FreePBX is Unable to Continue"), $e);
			}

			// global component
			if ( isset($currentcomponent) ) {
					modgettext::textdomain($module_name);
					try {
						$bmo->GuiHooks->doGUIHooks($module_name, $currentcomponent);
						echo  $currentcomponent->generateconfigpage();
					} catch(Exception $e) {
						die_freepbx(_("FreePBX is Unable to Continue"), $e);
					}
			}
		break;
}

$header = array();
$footer = array();

if ($quietmode) {
		// send the output buffer, should be sending just the page contents
		ob_end_flush();
} elseif ($fw_popover || $fw_popover_process) {
		$admin_template = $template = array();
		//get the page contents from the buffer
		$content = ob_get_contents();
		ob_end_clean();
		$fw_gui_html = '';

		// add header
		// Taken as is from the else just below this elseif
		// We're sending the popover, it needs a header if only for jQuery.
		// Already ok to pass popover awareness to header so popover.css is added
		$header['title']	= framework_server_name();
		$header['amp_conf']	= $amp_conf;
		$header['use_popover_css'] = ($fw_popover || $fw_popover_process);
		$o = FreePBX::create()->Less->generateMainStyles();
		$header['compiled_less_files'] = $o['compiled_less_files'];
		$header['extra_compiled_less_files'] = $o['extra_compiled_less_files'];
		show_view($amp_conf['VIEW_HEADER'], $header);

		//if we have a module loaded, load its css
		if (isset($module_name)) {
				$fw_gui_html .= framework_include_css();
		}

		// set the language so local module languages take
		set_language();

		// If processing posback (fw_popover_process) and there are errors then we
		// display again, otherwise we ignore the $content and prepare to process
		//
		$show_normal = $fw_popover_process ? fwmsg::errors() : true;
		if ($show_normal) {
				// provide beta status
				if (isset($fpbx_menu[$display]['beta']) && strtolower($fpbx_menu[$display]['beta']) == 'yes') {
					//TODO: Why is this in a global system variable?
						$fw_gui_html .= load_view($amp_conf['VIEW_BETA_NOTICE']);
				}
				$fw_gui_html .= $content;
				$popover_args['popover_mode'] = 'display';
		} else {
				$popover_args['popover_mode'] = 'process';
		}

		//send footer
		$footer['js_content'] = load_view($amp_conf['VIEW_POPOVER_JS'], $popover_args);

		$footer['extmap'] 				= !$footer['covert']
				? framework_get_extmap(true)
				: json_encode(array());
		$footer['module_name'] = $module_name;
		$footer['module_page'] = $module_page;
		$footer['benchmark_starttime'] = $benchmark_starttime;
		$footer['reload_needed'] = false; //we don't display the menu in this view so irrelivant
		//These changes will hide the excess footer which is just empty anyways, also it sets our body background to transparent
		//scripts in footer are still run eventhough it's hidden
		//hack into the footer and change the background to be transparent so it seems like we "belong" in the dialog box
		$footer['footer_content'] = "<script>$('body').css('background-color','transparent');$('#footer').hide()</script>";
		$footer['remove_rnav'] = true;
		$fw_gui_html .= load_view($amp_conf['VIEW_FOOTER'], $footer);
		echo $fw_gui_html;

} else {
		// Save the last module page normal view in the session. This is needed in some scenarios
		// such as a post back within a popOver destination box so that the drawselects() can be
		// properly generated within the context of the parent window that it will be filled back
		// in with.
		//
		$_SESSION['module_name']			= $module_name;
		$_SESSION['module_page']			= $module_page;

		$admin_template = $template = array();
		//get the page contents from the buffer
		$page_content		= ob_get_contents();
		ob_end_clean();

		//add header
		$header['title']	= framework_server_name();
		$header['amp_conf']	= $amp_conf;
		$header['use_popover_css'] = ($fw_popover || $fw_popover_process);

		$o = FreePBX::create()->Less->generateMainStyles();
		$header['compiled_less_files'] = $o['compiled_less_files'];
		$header['extra_compiled_less_files'] = $o['extra_compiled_less_files'];

		echo load_view($amp_conf['VIEW_HEADER'], $header);

		//if we have a module loaded, load its css
		if (isset($module_name)) {
			echo framework_include_css();
		}

		// set the language so local module languages take
		set_language();

		// send menu
		$menu['fpbx_menu']		= $fpbx_menu; //array of modules & settings
		$menu['display']		= $display; //currently displayed item
		$menu['authtype']		= $amp_conf['AUTHTYPE'];
		$menu['reload_confirm']	= $amp_conf['RELOADCONFIRM'];

		//add menu to final output
		echo load_view($amp_conf['VIEW_MENU'], $menu);

		// provide beta status
		if (isset($fpbx_menu[$display]['beta']) && strtolower($fpbx_menu[$display]['beta']) == 'yes') {
			echo load_view($amp_conf['VIEW_BETA_NOTICE']);
		}

		//send actual page content
		echo $page_content;

		//send footer
		$footer['covert'] 		= in_array($display, array('noauth', 'badrefer'))
				? true : false;
		$footer['extmap'] 				= !$footer['covert']
				? framework_get_extmap(true)
				: json_encode(array());
		$footer['module_name']			= $module_name;
		$footer['module_page']			= $module_page;
		$footer['benchmark_starttime']	= $benchmark_starttime;
		$footer['reload_needed']		= $footer['covert']
				? false : check_reload_needed();
		$footer['footer_content']		= load_view($amp_conf['VIEW_FOOTER_CONTENT'],
				$footer);
		if (!$footer['covert'] && function_exists("sysadmin_hook_framework_footer_view")) {
			$footer['sysadmin'] = sysadmin_hook_framework_footer_view();
		}
		$footer['covert'] ? $footer['no_auth'] 	= true : '';
		echo load_view($amp_conf['VIEW_FOOTER'], $footer);
}
?>