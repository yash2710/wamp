<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
require_once(dirname(__FILE__) . '/functions.inc.php');
global $db, $amp_conf;
set_time_limit(0);
include_once("functions.inc/functions_general.php");
if(file_exists($amp_conf['AMPWEBROOT'] . '/admin/.htaccess')){
	$htaccess =  file_get_contents($amp_conf['AMPWEBROOT'] . '/admin/.htaccess');
	if(!preg_match('/php_value max_input_vars 5000/', $htaccess)){
		$htaccess .= "\nphp_value max_input_vars 5000\n";
		file_put_contents($amp_conf['AMPWEBROOT'] . '/admin/.htaccess', $htaccess);
	}
}

$new = $db->query('SELECT `key` FROM endpoint_global');
if ($db->IsError($new)) {
	$newInstall = true;
} else {
	$newInstall = false;

	//check if we need to migrate XML to XML-API
	$q = "SELECT * FROM endpoint_global where `key` = 'legacyXML'";
	$r = $db->getAll($q, DB_FETCHMODE_ASSOC);
	if(!empty($r)){
		foreach($r as $key){
			if(!empty($key['key']) && $key['key'] == 'legacyXML'){
				if($key['values'] != 'Y'){
					include("migrateXML.php");
				}
			} else {
				include("migrateXML.php");
			}
		}
	} else {
		include("migrateXML.php");
	}
	
	$test = $db->query('SELECT * FROM endpoint_timezones');
	if ($db->IsError($test)) {
	} else {
			$sql[] = "DROP TABLE `endpoint_timezones`";
	}
	$test = $db->query('SELECT * FROM endpoint_brand');
	if ($db->IsError($test)) {
	} else {
			$sql[] = "DROP TABLE `endpoint_brand`";
	}
	$test = $db->query('SELECT * FROM endpoint_models');
	if ($db->IsError($test)) {
	} else {
			$sql[] = "DROP TABLE `endpoint_models`";
	}
	$test = $db->query('SELECT * FROM endpoint_xml');
	if ($db->IsError($test)) {
	} else {
			$sql[] = "DROP TABLE `endpoint_xml`";
	}
	$test = $db->query('SELECT * FROM endpoint_brands');
	if ($db->IsError($test)) {
	} else {
			$sql[] = "DROP TABLE `endpoint_brands`";
	}
	$test = $db->query('SELECT * FROM endpoint_aastra_extra');
	if ($db->IsError($test)) {
	} else {
			$sql[] = "DROP TABLE `endpoint_aastra_extra`";
	}
	$test = $db->query('SELECT `daylight` FROM endpoint_templates');
	if ($db->IsError($test)) {
		$sql[] = "ALTER TABLE endpoint_templates ADD COLUMN `daylight` VARCHAR(2)";
	}
	$test = $db->query('SELECT `models` FROM endpoint_templates');
	if ($db->IsError($test)) {
		$sql[] = "ALTER TABLE endpoint_templates ADD COLUMN `models` VARCHAR(200)";
	}
	
	$sql[] = "ALTER TABLE endpoint_basefiles MODIFY COLUMN `param` VARCHAR(120)";
	$sql[] = "ALTER TABLE endpoint_basefiles MODIFY COLUMN `model` VARCHAR(512)";

	$test = $db->query('SELECT `rebuild` FROM endpoint_extensions');
	if ($db->IsError($test)) {
		$sql[] = "ALTER TABLE endpoint_extensions ADD COLUMN `rebuild` VARCHAR(20)";
	}
	$test = $db->query('SELECT `dialpattern` FROM endpoint_templates');
	if ($db->IsError($test)) {
		$sql[] = "ALTER TABLE endpoint_templates ADD COLUMN `dialpattern` VARCHAR(512)";
	}

	//fix for polycom to eliminate info=
    $test = $db->query('SELECT `setting` FROM paging_autoanswer WHERE `useragent` = \'Polycom\'');
    if ($db->IsError($test)) {
        //do nothing
    } else {
        $sql[] = "update paging_autoanswer set `setting` = 'Alert-Info: Auto Answer' where `useragent` = 'Polycom'";
    }

    $test = $db->query('SELECT * FROM endpoint_basefiles');
    if($db->IsError($test)){
    	//do nothing
    } else {
    	$sql[] = "DELETE FROM endpoint_basefiles WHERE `template` = 'default'";
    }
	
	$test = $db->query('SELECT wEnable1 FROM endpoint_templates');
    if($db->IsError($test)){
		$sql[] = "ALTER TABLE endpoint_templates ADD COLUMN `wEnable1` VARCHAR(2)";
		$sql[] = "ALTER TABLE endpoint_templates ADD COLUMN `wssid1` VARCHAR(64)";
		$sql[] = "ALTER TABLE endpoint_templates ADD COLUMN `wsecurity1` VARCHAR(2)";
		$sql[] = "ALTER TABLE endpoint_templates ADD COLUMN `wWEPBits1` VARCHAR(5)";
		$sql[] = "ALTER TABLE endpoint_templates ADD COLUMN `wWPAPSKPass1` VARCHAR(64)";
		$sql[] = "ALTER TABLE endpoint_templates ADD COLUMN `wWPAPSKKey1` VARCHAR(64)";		
	}
	
	//change to varchar to allow leading 0's
	$sql[] = "ALTER TABLE endpoint_extensions MODIFY COLUMN `ext` VARCHAR(20)";

    foreach ($sql as $q) {
        $result = $db->query($q);
        if($db->IsError($result)){
            die_freepbx($result->getDebugInfo());
        }
    }
    unset($sql);
}

//create new common tables
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_buttons` (`id` INT(20) NOT NULL AUTO_INCREMENT, `brand` VARCHAR(20), `template_name` VARCHAR(45), `key` VARCHAR(60), `value` VARCHAR(120), `daylight` VARCHAR(2), UNIQUE KEY `id` (`id`))";
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_ext_buttons` (`id` INT(20) NOT NULL AUTO_INCREMENT, `ext` VARCHAR(20), `brand` VARCHAR(20), `template_name` VARCHAR(45), `key` VARCHAR(60), `value` VARCHAR(120), UNIQUE KEY `id` (`id`))";
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_templates` (`id` INT(20) NOT NULL AUTO_INCREMENT, `brand` VARCHAR(20), `default` VARCHAR(2), `template_name` VARCHAR(45), `destination` VARCHAR(40), `focus` VARCHAR(1), `answer` VARCHAR(1), `mute` VARCHAR(1), `barge` VARCHAR(1), `offhook` VARCHAR(1), `stutter` VARCHAR(1), `timeout` VARCHAR(2), `toneset` VARCHAR(12), `time_server` VARCHAR(1), `time_server_1` VARCHAR(30), `time_server_2` VARCHAR(30),`time_server_3` VARCHAR(30), `timezone` VARCHAR(30), `protocol` VARCHAR(10), `ftpuser` VARCHAR(40), `ftppass` VARCHAR(40), `ftpserver` VARCHAR(40), `extra` VARCHAR(400), `ext` VARCHAR(80), `missed` VARCHAR(1), `volume` VARCHAR(1), `echo` VARCHAR(1),  `callwaiting` VARCHAR(2), `features` VARCHAR(1024), `background` VARCHAR(256), `outgoing` VARCHAR(256), `firmware` VARCHAR(1) default '0', `multicastAddress` VARCHAR(1024), `multicastEnable` VARCHAR(2), `lineLabel` VARCHAR(2), `daylight` VARCHAR(2), `dialpattern` VARCHAR(512), `models` VARCHAR(200) default '0', UNIQUE KEY `id` (`id`))";
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_global` (`key` VARCHAR(25), `values` VARCHAR(2048), UNIQUE KEY `key` (`key`))";
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_extensions` (`ext` VARCHAR(20), `brand` VARCHAR(20), `mac` VARCHAR(20), `template` VARCHAR(45), `model` VARCHAR(25), `account` VARCHAR(12), `accessory` VARCHAR(12), `exp0` VARCHAR(20), `exp1` VARCHAR(20), `exp2` VARCHAR(20), `blf` VARCHAR(20), `blf_label` VARCHAR(20), `rebuild` VARCHAR(20), UNIQUE KEY `ext` (`ext`))";
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_models` (`brand` VARCHAR(20), `type` VARCHAR(10), `model` VARCHAR(25), `accounts` VARCHAR(3), `prgkey` VARCHAR(3), `softkeys` VARCHAR(3), `topsoftkey` VARCHAR(3), `extrakey` VARCHAR(3), `linekeys` VARCHAR(3), `horsoftkeys` VARCHAR(3), `speeddial` VARCHAR(3), `global` VARCHAR(1024), `firmware` VARCHAR(256), `exp` VARCHAR(3), UNIQUE KEY `model` (`model`))";
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_basefiles` (`id` int(11) NOT NULL auto_increment, `brand` varchar(20) default NULL, `template` varchar(45), `model` varchar(512) default NULL, `type` varchar(20) default NULL, `file` VARCHAR(40), `define` varchar(80) default NULL, `param` varchar(120) default NULL, `attrib` varchar(60) default NULL, `value` varchar(254) default NULL, `edited` varchar(1), `description` varchar(160), `OID` VARCHAR(10), PRIMARY KEY  (`id`))";
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_xml` (`api` VARCHAR(1), `app_name` VARCHAR(80), `url` VARCHAR(100), `9112i` VARCHAR(1), `6731i` VARCHAR(1), `6739i` VARCHAR(1), `9133i` VARCHAR(1), `9143i` VARCHAR(1), `480i` VARCHAR(1), `9480i` VARCHAR(1), `9480iCT` VARCHAR(1), `53i` VARCHAR(1), `55i` VARCHAR(1), `57i` VARCHAR(1), `6735i` VARCHAR(1), `6737i` VARCHAR(1), `51i` VARCHAR(1), `57iCT` VARCHAR(1), `675-1` VARCHAR(1), `675-2` VARCHAR(1), `675-3` VARCHAR(1), UNIQUE KEY `app_name` (`app_name`))";
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_images` (`key` VARCHAR(40), UNIQUE KEY `key` (`key`))";
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_firmware` (`brand` VARCHAR(20), `slot1` VARCHAR(20), `slot2` VARCHAR(20), `installed1` VARCHAR(1), `installed2` VARCHAR(1), UNIQUE KEY `brand` (`brand`))";
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_brand` (`id` INT(20) NOT NULL AUTO_INCREMENT, `brand` VARCHAR(20), `oui` VARCHAR(20), `basefile` VARCHAR(1024), `dialpattern` VARCHAR(512), UNIQUE KEY `id` (`id`))";
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_customExt` (`id` INT(20) NOT NULL AUTO_INCREMENT, `ext` VARCHAR(20), `secret` VARCHAR(60), `label` VARCHAR(20), `destination` VARCHAR(60), `sipPort` VARCHAR(20), UNIQUE KEY `id` (`id`))";
$sql[] = "CREATE TABLE IF NOT EXISTS `endpoint_timezones` (
	`id` int(11) NOT NULL auto_increment,
	`location` varchar(255) default NULL,
	`vtech` varchar(255) default NULL,
	`mbu` varchar(3) default NULL,
	`cortelco` varchar(3) default NULL,
	`mocet` varchar(3) default NULL,
	`and` varchar(35) default NULL,
	`panasonic` varchar(5) default NULL,
	`panasonic2` varchar(10) default NULL,
	`cisco` varchar(255) default NULL,
	`ciscoEnt` varchar(255) default NULL,
	`obihai` varchar(255) default NULL,
	`aastra` VARCHAR(30),
	`code` varchar(255) default NULL,
	`offset` varchar(8) default NULL,
	`snomv8` varchar(3) default NULL,
	`snom` varchar(3) default NULL,
	`grandstream` varchar(40) default NULL,
	`yealink` varchar(40) default NULL,
	`digium` varchar(40) default NULL,
	`dp715` varchar(40) default NULL,
	`incom` varchar(5) default NULL,
	`phoenix` varchar(60) default NULL,
	PRIMARY KEY  (`id`)) ENGINE=MyISAM AUTO_INCREMENT=168 DEFAULT CHARSET=latin1";

//fill tables
//                                                 id   location    	vtech                               MBU		cort    mocet   and                     panasonic   pan2        cisco   cisco-Enterprise                        obihai                      aastra              code    offset           snomv8     snom-m9 grandstream                                             yealink                                 digium                              dp715       Incom		Phoenix
$sql[] = "INSERT INTO `endpoint_timezones` (	  `id`, `location`, 	`vtech`, 							`mbu`, `cortelco`, `mocet`, `and`, 				`panasonic`, `panasonic2`, `cisco`, `ciscoEnt`, 						`obihai`, 					`aastra`, 			`code`, `offset`, 		`snomv8`, 	`snom`, `grandstream`, 											`yealink`, 								`digium`, 							`dp715`, 	`incom`,	`phoenix`)  VALUES
												('7', '-06:00',   		'America/Chicago',                  '6', 	'9',    '12',   'America/Chicago',      '-360',     '-60003',   '-6',   'Central Standard/Daylight Time',       '(Central Time)',           'US-Central',       'CST',  '-21600',        'USA-6',   '310',  'CST6CDT',                                              'United States-Central Time',           'America/Chicago',                  'TZE+6',    '44', 		'Central Time (US & Canada)'),
												('1', '-12:00',   		'',                                 '0',	'0',    '0',    'Etc/GMT-12',           '-720',     '-',        '',     'Dateline Standard Time',               '(Int\'l Dateline West)',   '',                 '',     '',              '',        '',     '',                                                     '',                                     '',                                 'TZA+12',   '', 		'Dateline'),
												('2', '-11:00',   		'Pacific/Pago_Pago',                '1',	'1',    '2',    'Etc/GMT-11',           '-660',     '-110001',  '-11',  'Samoa Standard Time',                  '(Samoa)',                  'AU-Perth',         'WST',  '-39600',        '',        '310',  'NZST-12NZDT-13,M10.1.0/02:00:00,M3.3.0/03:00:00',      'Samoa',                                'Pacific/Midway',                   'TZB+11',   '', 	'Samoa'),
												('3', '-10:00',   		'Pacific/Honolulu',                 '2',	'2',    '4',    'Etc/GMT-10',           '-600',     '-100001',  '-10',  'Hawaiian Standard Time',               '(Hawaii)',                 'US-Hawaii',        'HST',  '-36000',        'USA-10',  '310',  'HAW10',                                                'United States-Alaska-Aleutian',        'Pacific/Johnston',                 'TZC+10',   '36', 		'Hawaii'),
												('4', '-09:00',   		'America/Anchorage',                '3',	'3',    '6',    'Etc/GMT-9',            '-540',     '-90002',   '-9',   'Alaskan Standard/Daylight Time',       '(Alaska)',                 'US-Alaska',        'AKS',  '-32400',        'USA-9',   '299',  'AKST9AKDT',                                            'United States-Alaska Time',            'America/Anchorage',                'TZD+9',    '37', 		'Alaska'),
												('5', '-08:00',   		'America/Los_Angeles',              '4',	'4',    '8',    'America/Los_Angeles',  '-480',     '-80002',   '-8',   'Pacific Standard/Daylight Time',       '(Pacific Time)',           'US-Pacific',       'PST',  '-28800',        'USA-8',   '310',  'PST8PDT',                                              'United States-Pacific Time',           'America/Los_Angeles',              'TZE+8',    '39', 		'Pacific Time (US & Canada)'),
												('6', '-07:00',   		'America/Denver',                   '5',	'5',    '10',   'America/Denver',       '-420',     '-70002',   '-7',   'Mountain Standard/Daylight Time',      '(Mountain Time)',          'US-Mountain',      'MST',  '-25200',        'USA-7',   '310',  'MST7MDT',                                              'United States-Mountain Time',          'America/Denver',                   'TZE+7',    '41', 		'Mountain Time (US & Canada)'),
												('8', '-05:00',   		'America/New_York',                 '7',	'12',   '14',   'America/New_York',     '-300',     '-50001',   '-5',   'Eastern Standard/Daylight Time',       '(Eastern Time)',           'US-Eastern',       'EST',  '-18000',        'USA-5',   '310',  'EST5EDT',                                              'United States-Eastern Time',           'America/New_York',                 'TZE+5',    '47', 		'Eastern Time (US & Canada)'),
												('9', '-04:00',   		'America/Halifax',                  '9',	'15',   '16',   'Etc/GMT-4',            '-240',     '-40003',   '-4',   'Atlantic Standard/Daylight Time',      '(Atlantic Time)',          'CA-Atlantic',      'AST',  '-14400',        'CAN-4',   '310',  'AST4ADT',                                              'Canada(Halifax,Saint John)',           'America/Argentina/San_Juan',       'TZE+4',    '50', 		'Atlantic Time (Canada)'),
												('10', '-03:30',  		'America/St_Johns',                 '10',	'18',   '17',   'Etc/GMT-3',            '-210',     '',         '-3',   'Newfoundland Standard/Daylight Time',	'(Newfoundland)',           '',                 '',     '-12600',        'CAN-3.5', '',     'NST+3:30NDT+2:30,M4.1.0/00:01:00,M10.5.0/00:01:00',	'Canada-New Foundland(St.Johns)',       'America/St_Johns',                 'TZE+3',    '51', 		'Newfoundland'),
												('11', '-03:00',  		'America/Argentina/Buenos_Aires',   '11',	'19',   '18',   'Etc/GMT-3',            '-180',     '-30002',   '-3',   'SA Eastern Standard Time',				'(Buenos Aires,Greenland)', 'AR-Buenos aires',  'ART',  '-10800',        'ARG-3',   '310',  'NST+3NDT+2,M4.1.0/00:01:00,M10.5.0/00:01:00',    		'Argentina(Buenos Aires)',              'America/Argentina/Buenos_Aires',   'TZE+3',    '51', 		'Buenos Aires'),
												('12', '-02:00',  		'America/Sao_Paulo',                '13',	'22',   '20',   'Etc/GMT-2',            '-120',     '-20001',   '-2',   'Mid-Atlantic Standard/Daylight Time',	'(Mid-Atlantic)',           '',                 '',     '-7200',         'BRA-2',   '',     'NST+2NDT+1,M4.1.0/00:01:00,M10.5.0/00:01:00',                                                     'Brazil(no DST)',                       'Atlantic/South_Georgia',           'TZE+2',    '52', 		'Mid-Atlantic'),
												('13', '-01:00',  		'Atlantic/Azores',                  '14',	'23',   '22',   'Etc/GMT-1',            '-60',      '-10002',   '-1',   'Azores Standard/Daylight Time',		'',                         '',                 '',     '-3600',         'PRT-1',   '',     '',                                                     'Portugal(Azores)',                     'Atlantic/Azores',                  'TZE+1',    '0', 		'Azores'),
												('14', 'GMT',     		'GMT',                              '15',	'25',   '24',   'Etc/GMT',              '0',        '2',        '0',    'GMT Standard/Daylight Time',			'(London,Lisbon)',          'IE-Dublin',        'GMT',  '0',             'GBR-0',   '',     'GMT+0IST-1,M3.5.0/01:00:00,M10.5.0/02:00:00',          'GMT',                                  'Europe/Dublin',                    'TZN+0',    '3', 		'Dublin'),
												('15', '+01:00',  		'Europe/Brussels',                  '16',	'27',   '26',   'Etc/GMT+1',            '60',       '10003',    '+1',   'W. Europe Standard/Daylight Time',		'(Rome,Paris,Madrid)',      'IT-Rome',          'CET',  '3600',          'FRA+1',   '',     'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00',         'Italy(Rome)',                          'Europe/Paris',                     'TZN+1',    '4', 		'Paris'),
												('16', '+02:00',  		'Europe/Paris',                     '17',	'32',   '28',   'Etc/GMT+2',            '120',      '20002',    '+2',   'E. Europe Standard/Daylight Time',     '(Athens,Cairo)',           'GR-Athens',        'EET',  '7200',          'EGY+2',   '',     'EET-2EEST-3,M3.5.0/03:00:00,M10.5.0/04:00:00',         'Greece(Athens)',                       'Europe/Athens',                    'TZN+2',    '9', 		'Athens'),
												('17', '+03:00',  		'Europe/Moscow',                    '18',	'38',   '30',   'Etc/GMT+3',            '180',      '30002',    '+3',   'Russian Standard/Daylight Time',		'(Moscow,Baghdad)',         'RU-Moscow',        'MSK',  '10800',         'RUS+3',   '',     'MSK-3MSD,M3.5.0/2,M10.5.0/3',                          'Russia(Moscow)',                       'Asia/Baghdad',                     'TZN+3',    '13', 		'Baghdad'),
												('18', '+03:30',  		'Asia/Tehran',                      '19',	'42',   '31',   'Etc/GMT+3',            '210',      '35001',    '+3',   'Iran Standard/Daylight Time',			'(Tehran)',                 '',                 '',     '12600',         'IRN+3.5', '',     '',                                                     'Iran(Teheran)',                        'Asia/Tehran',                      'TZN+3',    '16', 		'Tehran'),
												('19', '+04:00',  		'Asia/Tbilisi',                     '20',	'43',   '32',   'Etc/GMT+4',            '240',      '40001',    '+4',   'Arabian Standard Time',				'(Abu Dhabi)',              'OM-Muscat',        'GST',  '14400',         'RUS+4',   '',     'CET-1CEST-2,M3.5.0/02:00:00,M10.5.0/03:00:00',         'Georgia(Tbilisi)',                     'Asia/Dubai',                       'TZN+4',    '17', 		'Abu Dhabi'),
												('20', '+04:30',  		'Asia/Tbilisi',                     '21',	'45',   '33',   'Etc/GMT+4',            '270',      '45001',    '+4',   'Afghanistan Standard Time',			'(Kabul)',                  '',                 '',     '16200',         '',        '',     '',                                                     '',                                     'Asia/Dubai',                       'TZN+4',    '18', 		'Afghanistan'),
												('21', '+05:00',  		'Asia/Karachi',                     '22',	'46',   '34',   'Etc/GMT+5',            '300',      '50001',    '+5',   'West Asia Standard Time',				'(Islamabad,Karachi)',      '',                 '',     '18000',         'RUS+5',   '',     '',                                                     'Pakistan(Islamabad)',                  'Asia/Karachi',                     'TZN+5',    '21', 		'Islamabad'),
												('22', '+05:30',  		'Asia/Karachi',                     '23',	'48',   '35',   'Etc/GMT+5',            '330',      '55002',    '+5',   'India Standard Time',					'(New Delhi)',              '',                 '',     '19800',         'IND+5.5', '',     '',                                                     'India(Calcutta)',                      'Asia/Karachi',                     'TZN+5',    '19', 		'Bombay'),
												('23', '+05:45',  		'Asia/Karachi',                     '24',	'49',   '35',   'Etc/GMT+5',            '345',      '57501',    '+5',   'Central Asia Standard Time',			'(Kathmandu)',              '',                 '',     '20700',         '',        '',     '',                                                     '',                                     'Asia/Karachi',                     'TZN+5',    '21', 		''),
												('24', '+06:00',  		'Asia/Novosibirsk',                 '25',	'50',   '36',   'Etc/GMT+6',            '360',      '60001',    '+6',   'SE Asia Standard Time',				'',                         '',                 '',     '21600',         'RUS+6',   '',     '',                                                     'Russia(Novosibirsk,Omsk)',             'Asia/Dhaka',                       'TZN+6',    '22', 		'Dhaka'),
												('25', '+06:30',  		'Asia/Bangkok',                     '26',	'53',   '37',   'Etc/GMT+6',            '390',      '65001',    '+6',   'SE Asia Standard Time',				'(Yangon)',                 '',                 '',     '23400',         '',        '',     '',                                                     '',                                     'Asia/Bangkok',                     'TZN+6',    '23', 		'Bangkok'),
												('26', '+07:00',  		'Asia/Bangkok',                     '27',	'54',   '38',   'Etc/GMT+7',            '420',      '70001',    '+7',   'Taipei Standard Time',					'(Bangkok,Jakarta)',        '',                 '',     '',              'RUS+7',   '',     '',                                                     'Thailand(Bangkok)',                    'Asia/Bangkok',                     'TZN+7',    '23', 		''),
												('27', '+08:00',  		'Asia/Singapore',                   '28',	'56',   '40',   'Etc/GMT+8',            '480',      '80004',    '+8',   'China Standard/Daylight Time',			'(Beijing,HK,Singapore)',   'HK-Hong kong',     'HKS',  '28800',         'SGP+8',   '',     'SGT-8',                                                'Singapore(Singapore)',                 'Asia/Singapore',                   'TZN+8',    '28', 		'Beijing'),
												('28', '+09:00',  		'Asia/Tokyo',                       '29',	'61',   '42',   'Etc/GMT+9',            '540',      '90001',    '+9',   'Tokyo Standard Time',					'(Tokyo,Seoul)',            '',                 '',     '32400',         'JPN+9',   '',     '',                                                     'Korea(Seoul)',                         'Asia/Tokyo',                       'TZN+9',    '32', 		'Tokyo'),
												('29', '+09:30',  		'Australia/Adelaide',               '30',	'64',   '43',   'Etc/GMT+9',            '570',      '95001',    '+9',   'AUS Central Standard Time',			'(Adelaide)',               '',                 '',     '34200',         'AUS+9.5', '',     '',                                                     'Australia(Adelaide)',                  'Australia/Adelaide',               'TZN+9',    '32', 		'Adelaide'),
												('30', '+10:00',  		'Australia/Sydney',                 '31',	'66',   '44',   'Etc/GMT+10',           '600',      '100001',   '+10',  'E. Australia Standard Time',			'(Sydney,Guam)',            '',                 '',     '36000',         'AUS+10',  '',     '',                                                     'Australia(Sydney,Melbourne,Canberra)', 'Australia/Melbourne',              'TZN+10',   '34', 		'Melbourne'),
												('31', '+11:00',  		'Pacific/Noumea',                   '32',	'71',   '46',   'Etc/GMT+11',           '660',      '110001',   '+11',  'Central Pacific Standard Time',		'(Solomon Is.)',            '',                 '',     '39600',         'NCL+11',  '',     '',                                                     'New Caledonia(Noumea)',                'Pacific/Noumea',                   'TZN+11',   '', 		'SolomanIs.'),
												('32', '+12:00',  		'Pacific/Auckland',                 '33',	'72',   '48',   'Etc/GMT+12',           '720',      '120002',   '+12',  'Fiji Standard Time',					'(Fiji,Auckland)',          '',                 '',     '43200',         'RUS+12',  '',     '',                                                     'New Zeland(Wellington,Auckland)',      'Pacific/Fiji',                     'TZN+12',   '35', 		'Fiji'),
												('33', '+13:00',  		'Pacific/Tongatapu',                '34',	'',     '50',   'Etc/GMT+13',           '780',      '130001',   '+13',  'New Zealand Standard/Daylight Time',	'',                         '',                 '',     '46800',         'TON+13',  '',     '',                                                     'Tonga(Nukualofa)',                     '',                                 'TZN+13',   '', 		''),
												('34', '-07:00 No DST', 'America/Phoenix',					'5',	'5',	'10', 	'America/Phoenix',		'-420',		'-70001',	'-7',	'US Mountain Standard Time',			'(Mountain Time)',			'US-Mountain',		'MST',	'-25200',		 'USA2-7',	'2880',	'MST7',													'United States-MST no DST',				'US/Arizona',						'MST7', 	'40', 		'Mountain Time (US & Canada)')
												";


$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('aastra', '00085D')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('aastra', '0010BC')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('aastra', 'AC44F2')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('algo', '0022EE')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('and', '2046F9')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('audiocodes', '00908F')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '00036B')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '00000C')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '000142')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '000143')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '000163')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '000164')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '000196')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '000197')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '0001C7')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '0001C9')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '000F23')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '0013C4')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '0016C8')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001818')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '00175A')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001795')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001A2F')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001C58')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001DA2')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '002155')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '002290')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '000E84')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '000E38')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '00070E')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001BD4')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001930')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '0019AA')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001D45')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001EF7')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '000E08')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '1CDF0F')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '30E4DB')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '3CCE73')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '503DE5')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '54781A')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '58BFEA')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '649EF3')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '68EFBD')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '5475D0')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '70CA9B')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '708105')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', 'B4E9B0')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', 'C46413')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', 'C89C1D')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', 'D0D0FD')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', 'D824BD')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', 'E05FB9')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', 'E02F6D')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '0014BF')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '0018F8')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', 'C471FE')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', 'CCEF48')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001AA1')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001B2A')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001C58')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '001D45')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '002155')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '002255')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '002290')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '002304')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '00260B')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', '0026CB')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cisco', 'E8EDF3')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cortelco', '00A859')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('cyberdata', '0020F7')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('digium', '000FD3')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('grandstream', '000B82')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('incom', '00032A')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('mitel', '08000F')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('mocet', '001915')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('obihai', '9CADEF')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('panasonic', '0080F0')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('panasonic', '080023')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('phoenix', '0050C2')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('panasonic', '0080F0')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('polycom', '0004F2')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('polycom', '00907A')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('sangoma', '005058')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('snom', '000413')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('uniden', '00087B')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('vtech', '00122A')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('xorcom', '642400')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `oui`) VALUES ('yealink', '001565')";

//Descriptions for Basefile
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Aastra', 		'x+#|xx+*', 'Aastra configs are a direct 1-to-1 relationship.<br />PARAMETER = VALUE.  <br />We wrap the values with quotes in case of symbol or non-standard characters are used in passwords/values/etc')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Algo', 		'x+#|xx+*', 'Algo configs are a direct 1-to-1 relationship.  <br />PARAMETER = VALUE.<br />The part of the parameter before the period is the section.')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('And', 			'x+#|xx+*', 'AND uses an XML style. <br /><br />Every basefile entry requires a \"Section\" to be set.<br />For instance:<br /><ul><li>To add an entry to the SIPConfig section, \"Section\" needs to be set to SIPConfig (case sensitive).</li><li>To create a new section, \"Section\" needs to be the section it will be within (use \"TOP\" if it should not be in a section), PARAMETER is the new sections name, and VALUE would be \"PARENT\" to define it as a new section.</li></ul>')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Audiocodes', 	'x+#|xx+*', 'Audiocodes configs are a direct 1-to-1 relationship.<br />PARAMETER=VALUE.<br />The PARAMETER includes the path to the setting.')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Cisco', 		'(*x.|**xx|[3469]11|0|00|[2-9]xxxxxx|1xxx[2-9]xxxxxxS0|xxxxxxxxxxxx.)', '<ul><li>SPA Series</li><ul><li>Cisco SPA Series configs are XML based.<br />All entries are &lt;PARAMETER ua=ATTRIBUTE>VALUE&lt;/PARAMETER></li></ul><li>Enterprise Series</li><ul><li>Cisco Enterprise uses an XML style. <br /><br />Every basefile entry requires a \"Section\" to be set.<br />For instance:<br /><ul><li>To add an entry to the SIPConfig section, \"Section\" needs to be set to SIPConfig (case sensitive).</li><li>To create a new section, \"Section\" needs to be the section it will be within (use \"TOP\" if it should not be in a section), PARAMETER is the new sections name, and VALUE would be \"PARENT\" to define it as a new section.</li></ul></li></ul><li>Legacy Enterprise Series</li><ul><li>Cisco Legacy Enterprise configs are a direct 1-to-1 relationship.</li></ul></ul>')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Cortelco', 	'x+#|xx+*', 'Cortelco uses a nested XML style. <br /><br />Every basefile entry requires a \"Section\" to be set.<br />For instance:<br /><ul><li>To add an entry to the Port_Config section, \"Section\" needs to be set to Port_Config (case sensitive).</li><li>To create a new section, \"Section\" needs to be the section it will be within (use \"TOP\" if it should not be in a section), PARAMETER is the new sections name, and VALUE would be \"PARENT\" to define it as a new section.</li></ul>')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Cyberdata', 	'x+#|xx+*', 'Cyberdata uses a nested XML style. <br /><br />Every basefile entry requires a \"Section\" to be set.<br />For instance:<br /><ul><li>To add an entry to the DeviceSettings section, \"Section\" needs to be set to DeviceSettings (case sensitive).</li><li>To create a new section, \"Section\" needs to be the section it will be within (use \"TOP\" if it should not be in a section), PARAMETER is the new sections name, and VALUE would be \"PARENT\" to define it as a new section.</li></ul>')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Digium', 		'[0-8]xxxxx|911|9411|9611|9011xxx.T3|91xxxxxxxxxx|9[2-9]xxxxxx|*xx.T3|[0-8]xx.T3', 'Digium uses a nested XML style. <br /><br />Every basefile entry requires a \"Section\" to be set.<br />For instance:<br /><ul><li>To add an entry to the codecs section, \"Section\" needs to be set to codecs (case sensitive).</li><li>To create a new section, \"Section\" needs to be the section it will be within (use \"TOP\" if it should not be in a section), PARAMETER is the new sections name, and VALUE would be \"PARENT\" to define it as a new section.</li><li>To add to an account section, the \"Section\" should be the account number.  The \"Parameter\" should be the account number before the section.  ie:0acount_id</li></ul>')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Grandstream', 	'{x+|*x+}', 'Grandstream configs are a direct 1-to-1 relationship.  <br />PARAMETER = VALUE.<br />Default entries have a description above.')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Incom', 		'', 		'Incom configs are a direct 1-to-1 relationship with sections.<br />Every basefile entry requires a \"Section\" to be set.<br />For instance:<br /><ul><li>To add an entry to the cfg:/phone/config/voip/sipAccount0.cfg,account=0;reboot=0 section, \"Section\" needs to be set to cfg:/phone/config/voip/sipAccount0.cfg,account=0;reboot=0 (case sensitive).</li><li>To create a new section, \"Section\" needs to be the new section name. PARAMETER is the first setting, and VALUE would be the setting value.</li>')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Mitel', 		'x+#|xx+*', 'Mitel uses a nested XML style, but simplified.  All entries are treated as a direct 1-to-1 relationship.<br />PARAMETER = VALUE<br />The small amount of nesting is done by the module.')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Mocet', 		'x+#|xx+*', 'Mocet uses a nested XML style. <br /><br />Every basefile entry requires a \"Section\" to be set.<br />For instance:<br /><ul><li>To add an entry to the codecs section, \"Section\" needs to be set to codecs (case sensitive).</li><li>To create a new section, \"Section\" needs to be the section it will be within (use \"TOP\" if it should not be in a section), PARAMETER is the new sections name, and VALUE would be \"PARENT\" to define it as a new section.</li><li>Every entry should also have a \"Type\" setting.  This defines what the value type should be. Either \"string\" for text or \"integer\" for a number.')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Obihai', 		'(*xx|[1-9]S9|[1-9][0-9]S9|911|**0|***|#|**8(Mbt)|**1(Msp1)|**2(Msp2)|**3(Msp3)|**4(Msp4)|**9(Mpp)|(Mpli))', 'Obihai configs are a direct 1-to-1 relationship.  <br />PARAMETER = VALUE.  ')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Panasonic', 	'', '<ul><li>Legacy Panasonic configs are a direct 1-to-1 relationship.  <br />PARAMETER = VALUE.  <br />We wrap the values with quotes in case of symbol or non-standard characters are used in passwords/values/etc</li><li>Current Panasonic uses a nested XML style. <br /><br />Every basefile entry requires a \"Section\" to be set.<br />For instance:<br /><ul><li>To add an entry to the codecs section, \"Section\" needs to be set to codecs (case sensitive).</li><li>To create a new section, \"Section\" needs to be the section it will be within (use \"TOP\" if it should not be in a section), PARAMETER is the new sections name, and VALUE would be \"PARENT\" to define it as a new section.</li>')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Phoenix', 		'x+#|xx+*', 'Phoenix configs are a direct 1-to-1 relationship.<br />PARAMETER = VALUE.')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Polycom', 		'[2-9]11|0T|011xxx.T|[0-1][2-9]xxxxxxxxx|[2-9]xxxxxxxxx|[2-9]xxxT', 'Polycom uses a nested XML style with multiple files. <br /><br />Every basefile entry requires the following.<ul><li>A \"File\".<br />Needs to be the name of the file the setting will be in.  ie:mac-legacy, features, etc.  If you do not know what the file name is, click on an existing setting.</li><li>A \"Section\".<br />For instance:<br /><ul><li>To add an entry to the Port_Config section, \"Section\" needs to be set to Port_Config (case sensitive).</li><li>To create a new section, \"Section\" needs to be the section it will be within (use \"TOP\" if it should not be in a section), PARAMETER is the new sections name, and VALUE would be \"PARENT\" to define it as a new section.</li></ul></li><li>A \"Parameter\".<br />This is the setting name.</li><li>A \"Value\".<br />This is the actual setting.</li></ul>')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Sangoma', 		'x+#|xx+*', 'Sangoma configs are a direct 1-to-1 relationship.<br />PARAMETER = VALUE.')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Snom', 		'x+#|xx+*', 'Snom uses a nested XML style, but simplified. All entries are treated as a direct 1-to-1 relationship.<br />PARAMETER = VALUE<br />The small amount of nesting is done by the module.')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Uniden', 		'x+#|xx+*', 'Uniden configs are a direct 1-to-1 relationship. <br />PARAMETER = VALUE.<br />All Parameters are wrapped with % by the module.  You do not need to enter these.')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Vtech', 		'x+P', 'VTech configs are a direct 1-to-1 relationship.<br />PARAMETER = VALUE.')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Xorcom', 		'', 'Xorcom configs are a direct 1-to-1 relationship with sections.<br />Every basefile entry requires a \"Section\" to be set.<br />For instance:<br /><ul><li>To add an entry to the cfg:/phone/config/voip/sipAccount0.cfg,account=0;reboot=0 section, \"Section\" needs to be set to cfg:/phone/config/voip/sipAccount0.cfg,account=0;reboot=0 (case sensitive).</li><li>To create a new section, \"Section\" needs to be the new section name. PARAMETER is the first setting, and VALUE would be the setting value.</li>')";
$sql[] = "INSERT INTO `endpoint_brand` (`brand`, `dialpattern`, `basefile`) VALUES ('Yealink', 		'', 'Yealink has two styles.<ul><li>Current (V70) configs are a direct 1-to-1 relationship. PARAMETER = VALUE.</li><li>Legacy configs  are a direct 1-to-1 relationship with sections.<br />Every basefile entry requires a \"Section\" to be set.<br />For instance:<br /><ul><li>To add an entry to the cfg:/phone/config/voip/sipAccount0.cfg,account=0;reboot=0 section, \"Section\" needs to be set to cfg:/phone/config/voip/sipAccount0.cfg,account=0;reboot=0 (case sensitive).</li><li>To create a new section, \"Section\" needs to be the new section name. PARAMETER is the first setting, and VALUE would be the setting value.</li></ul></li></ul>')";



//api xmls
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Apps', 'http://__line1Dest__:__restapps__/applications.php/restapps/main?user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Call Flow', 'http://__line1Dest__:__restapps__/applications.php/daynight/main?user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Call Forward', 'http://__line1Dest__:__restapps__/applications.php/callforward/main?user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Conference', 'http://__line1Dest__:__restapps__/applications.php/conferences/main?user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Contacts', 'http://__line1Dest__:__restapps__/applications.php/contactmanager/main?user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-DND', 'http://__line1Dest__:__restapps__/applications.php/donotdisturb/main?user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Follow Me', 'http://__line1Dest__:__restapps__/applications.php/findmefollow/main?user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Login', 'http://__line1Dest__:__restapps__/applications.php/endpoint/main?user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Parking', 'http://__line1Dest__:__restapps__/applications.php/parking/main?linestate=$\$LINESTATE$$&user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Presence', 'http://__line1Dest__:__restapps__/applications.php/presencestate/main?user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Queues', 'http://__line1Dest__:__restapps__/applications.php/queues/main?user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Queue Agent', 'http://__line1Dest__:__restapps__/applications.php/queues/agent?user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Time Conditions', 'http://__line1Dest__:__restapps__/applications.php/timeconditions/main?user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Transfer VM', 'http://__line1Dest__:__restapps__/applications.php/voicemail/transfer?linestate=$\$LINESTATE$$&user=')";
$sql[] = "INSERT INTO `endpoint_xml` (`api`, `app_name`, `url`) VALUES ('1', 'REST-Voicemail', 'http://__line1Dest__:__restapps__/applications.php/voicemail/main?linestate=$\$LINESTATE$$&user=')";


// run what we have to create tables
foreach ($sql as $q) {
	$result = $db->query($q);
	if($db->IsError($result)){
		die_freepbx($result->getDebugInfo());
	}
}
unset($sql);

//get globals
$sipSettings = $db->getAll("Select * from sipsettings", DB_FETCHMODE_ASSOC);
foreach($sipSettings as $k=>$v){
	if($v['keyword'] == 'externhost_val'){$ext=$v['data'];}
}


$listen = system("/bin/grep '^Listen.*' /etc/httpd/conf/httpd.conf");
$port = substr($listen, 7);
$netface = endpoint_get_ifs();
$int = $netface[0]['IPADDR'];


//TODO: only install brands with templates
include('install/aastraInstall.php');
include('install/algoInstall.php');
include('install/andInstall.php');
include('install/audiocodesInstall.php');
include('install/ciscoInstall.php');
include('install/cortelcoInstall.php');
include('install/mocetInstall.php');
include('install/cyberdataInstall.php');
include('install/digiumInstall.php');
include('install/grandstreamInstall.php');
include('install/incomInstall.php');
include('install/mitelInstall.php');
include('install/obihaiInstall.php');
include('install/panasonicInstall.php');
include('install/phoenixInstall.php');
include('install/polycomInstall.php');
include('install/sangomaInstall.php');
include('install/snomInstall.php');
include('install/unidenInstall.php');
include('install/vtechInstall.php');
include('install/yealinkInstall.php');
include('install/xorcomInstall.php');

if ($newInstall == true) {
	$sql[] = "INSERT INTO `endpoint_global` (`key`, `values`) VALUES ('internal', '$int'), ('external', '$ext'), ('sipPort', '5060'), ('port', '$port'), ('profile', 'sample'), ('admin_password', '222222'), ('no_vm_password', '1234'), ('dhcp', '1'), ('ipaddress', 'both'), ('user_password', '111111')";
	$sql[] = "INSERT INTO `endpoint_global` (`key`, `values`) VALUES ('migrate', '12')";
}

foreach ($sql as $q) {
	$result = $db->query($q);
	if($db->IsError($result)){
		die_freepbx($result->getDebugInfo());
	}
}

if(!file_exists('/tftpboot/images')){system('/bin/mkdir /tftpboot/images', $result);}
if(!file_exists('/tftpboot/images/originals')){system('/bin/mkdir /tftpboot/images/originals', $result);}
if(!file_exists('/tftpboot/images/formatted')){system('/bin/mkdir /tftpboot/images/formatted', $result);}
if(!file_exists('/tftpboot/firmwaredownloads')){system('/bin/mkdir /tftpboot/firmwaredownloads', $result);}
$pwd = dirname(__FILE__);
if(!file_exists($pwd . '/images')){system("/bin/ln -s /tftpboot/images/ $pwd/images", $result);}
if(file_exists($pwd . '/images/images')){system("/bin/unlink /tftpboot/images/images", $result);}

if(!function_exists('endpoint_template_changed')){
	//mark all extensions using a specific template as "needs rebuild"
	function endpoint_template_changed($template = null, $brand = null){
	        global $db;
	        if(empty($template) || empty($brand)){
	                //probably install
	                $query = "UPDATE endpoint_extensions SET `rebuild` = '1'";
	        } else {
	                $query = "UPDATE endpoint_extensions SET `rebuild` = '1' WHERE `brand` = '$brand' AND `template` = '$template'";
	        }
	        $sql = $db->query($query);
	}
}


if ($newInstall != true) {
	endpoint_template_changed(); //mark all extensions as changed
}
//grandstream sample phonebook
if(!file_exists('/tftpboot/phonebook.xml')){
	$phonebook = '<AddressBook>
  <Contact>
    <LastName>Enter last name here</LastName>
    <FirstName>Enter first name here</FirstName>
    <Phone>
      <phonenumber>Enter phone number here</phonenumber>
      <accountindex>Enter account index here</accountindex>
    </Phone>
    <Groups>
      <groupid>Enter group ID here</groupid>
    </Groups>
  </Contact>
</AddressBook>';
	$fileloc = '/tftpboot/phonebook.xml';
	file_put_contents($fileloc, $phonebook);
	$chmods = system("/bin/chmod 755 " . $fileloc, $retval);
}

	unset($sql);
	
	//check if we need to migrate to 12
	//put it here because we rebuild configs and need the rest in place
	$q = "SELECT * FROM endpoint_global where `key` = 'migrate'";
	$r = $db->getAll($q, DB_FETCHMODE_ASSOC);

	if(!empty($r)){
		foreach($r as $key){
			if(!empty($key['key']) || $key['key'] == 'migrate'){
				if($key['values'] < '12'){
					include("migrate12.php");
				}
			} else {
				include("migrate12.php");
			}
		}
	} else {
		include("migrate12.php");
	}

if(!file_exists($pwd . '/assets/less/cache')){system('/bin/mkdir ' . $pwd . '/assets/less/cache', $result);}
if(file_exists($pwd . '/assets/less/cache')){system('/bin/chmod 775 ' . $pwd . '/assets/less/cache', $result);}

//we only install recommended for digium, so we will skip this at install.
//shell_exec($amp_conf['AMPBIN'] . '/endpoint_firmware.php > /dev/null & echo $!');

endpoint_get_config('asterisk');

function endpoint_get_ifs() {
	//scan directory for all possible interface config files
	$ifs = scandir('/etc/sysconfig/network-scripts/');
	//loop through the files we found
	foreach ($ifs as $if) {
		if (strpos(trim($if),'ifcfg-eth') === 0 OR strpos(trim($if),'ifcfg-wlan') === 0) {
			$my_if =  str_replace('ifcfg-', '', $if);

			//if its an eth or a wlan, parse the file and add to the array
			$my_if_file = '/etc/sysconfig/network-scripts/' . $if;
			if (file_exists($my_if_file) && is_readable($my_if_file)) {
				$comment = array(';', '#');
				//read file as an array
				$line = file($my_if_file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
				foreach ($line as $ln) {
					if (!in_array(substr($ln, 0, 1), $comment)) {
						$part = explode('=', $ln);
						//dbug('$f_line', $f_line);
						$this_face[trim($part[0])] = trim($part[1]);
					}
				}
			}
			//ensure we have DEVICE set
			if (!isset($this_face['DEVICE'])) {
				$this_face['DEVICE'] = $my_if;
			}
			//make sure bootproto isnt set to static
			if (isset($this_face['BOOTPROTO']) && $this_face['BOOTPROTO'] == 'static') {
				$this_face['BOOTPROTO'] = 'none';
			}
			//set possible values so there not blank
			$this_face['BOOTPROTO']	= isset($this_face['BOOTPROTO'])	? $this_face['BOOTPROTO']	: '';
			$this_face['ONBOOT']	= isset($this_face['ONBOOT'])		? $this_face['ONBOOT']		: '';
			$this_face['IPADDR']	= isset($this_face['IPADDR'])		? $this_face['IPADDR']		: '';
			$this_face['NETMASK']	= isset($this_face['NETMASK'])		? $this_face['NETMASK']		: '';
			$this_face['GATEWAY']	= isset($this_face['GATEWAY'])		? $this_face['GATEWAY']		: '';
			$this_face['HOSTNAME']	= isset($this_face['HOSTNAME'])		? $this_face['HOSTNAME']	: '';
			$this_face['ONBOOT']	= isset($this_face['ONBOOT'])		? $this_face['ONBOOT']		: '';
			$this_face['HWADDR']	= isset($this_face['HWADDR'])		? $this_face['HWADDR']		: '';
			//add a mac address if its missing and we can find it
			if (!$this_face['HWADDR'] && $this_face['type'] == 'physical') {
				//get results from `ip`
				exec('ip -f link addr show ' . $this_face['DEVICE'], $ip_ret);
				if ($ip_ret) {
					$r = preg_split('/\s+/', $ip_ret[1]); //split based on whitespace
					if ($r[1] == 'link/ether') { //make sure array is formated as we excpect it
						$this_face['HWADDR'] = $r[2];
					}
				}
			}
			//add settings to array
			$ret[] = $this_face;
		}
	}

	//now, iterate over the phsicaly adapters to ensure that we didnt miss any
	if(isset($pys_net) && is_array($pys_net)){
            foreach ($pys_net as $pys => $stat) {
		if  (strpos($pys,'eth') === 0 OR strpos($pys,'wlan') === 0) {
                    if (!isset($var['netface'][$pys])) {
                        $this_face['DEVICE']	= $pys;
                        $this_face['BOOTPROTO']	= '';
                        $this_face['ONBOOT']	= '';
                        $this_face['IPADDR']	= '';
                        $this_face['NETMASK']	= '';
                        $this_face['GATEWAY']	= '';
                        $this_face['HOSTNAME']	= '';
                        $this_face['stats'] 	= $stat;
                        $this_face['type'] 		= 'physical';
                        //add a mac address if we can find it
                        //get results from `ip`
                        exec('ip -f link addr show ' . $this_face['DEVICE'], $ip_ret);
                        if ($ip_ret) {
                            $r = preg_split('/\s+/', $ret[1]); //split based on whitespace
                            if ($r[1] == 'link/ether') { //make sure array is formated as we excpect it
                                $this_face['HWADDR'] = $r[2];
                            }
                        }
                        $ret[] = $this_face;
                    }
		}
            }
        }
	return $ret;
}
?>
