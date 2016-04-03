#!/usr/bin/php -q
<?php
//Bootstrap FreePBX
$restrict_mods = array(
      'sysadmin' => true,
);

if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
      include_once('/etc/asterisk/freepbx.conf');
}

//load license
include_once('/var/lib/asterisk/agi-bin/LoadLicenseIfExists.php');

//if we dont have a valid license file installed, we wont be able to load sysadmin_get_license(),
//so return something usefull here
if (!function_exists('sysadmin_get_license')) {
      function sysadmin_get_license() {
            return false;
      }
}

//Ensure user is licensed for sysadmin
$lic = sysadmin_get_license();
if (date('Ymd') > str_replace('-', '',$lic['sysadmin_exp'])) {
      return false;
}


$matches = array();
$safe_asterisk_file = '/usr/sbin/safe_asterisk';
$safe_asterisk = file_get_contents($safe_asterisk_file);

preg_match_all('/(#?)NOTIFY=([A-Za-z0-9@.]*)\s*#/', $safe_asterisk, $matches);

//Get the email from the db
$email = sysadmin_get_storage_email();

//If the email is empty, exit out
if (empty($email['safe_asterisk'])) {
      return false;
}

if ($matches[1][0] === '#') {
      //Uncomment the Notify line
      $safe_asterisk = str_replace($matches[0][0], substr($matches[0][0], 1), $safe_asterisk);
}

$safe_asterisk = str_replace($matches[2][0], $email['safe_asterisk'], $safe_asterisk);

file_put_contents($safe_asterisk_file, $safe_asterisk);
