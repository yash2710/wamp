<?php

/**
 * FreePBX DAHDi Config Module
 *
 * Copyright (c) 2009, Digium, Inc.
 *
 * Author: Ryan Brindley <ryan@digium.com>
 *
 * This program is free software, distributed under the terms of
 * the GNU General Public License Version 2. 
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

global $db;
global $asterisk_conf;

$tables = array('digiumaddoninstaller_system', 
	'digiumaddoninstaller_addons', 
	'digiumaddoninstaller_downloads', 
	'digiumaddoninstaller_addons_downloads', 
	'digiumaddoninstaller_downloads_bits', 
	'digiumaddoninstaller_downloads_ast_versions', 
	'digiumaddoninstaller_registers'
);
foreach ($tables as $table) {
	$sql = "DROP TABLE IF EXISTS {$table}";
	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
	unset($result);
}

// end of file
