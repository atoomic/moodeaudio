<?php
/**
 * PlayerUI Copyright (C) 2013 Andrea Coiutti & Simone De Gregori
 * Tsunamp Team http://www.tsunamp.com
 *
 * This Program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3, or (at your option)
 * any later version.
 *
 * This Program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TsunAMP; see the file COPYING.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Moode Audio Player (C) 2014 Tim Curtis
 * http://moodeaudio.org
 *
 * 2014-08-23 1.0 TC initial rewrite
 * 2016-02-27 2.5 TC rewrite for pre-3.0
 * 2016-06-07 2.6 TC moodeOS 1.0
 * 2016-08-28 2.7 TC add capability to run php cmds (vol.php)
 *
 */
 
require_once dirname(__FILE__) . '/../inc/playerlib.php';

if (isset($_GET['cmd']) && $_GET['cmd'] === '') {
	echo 'command missing';
}
elseif (stripos($_GET['cmd'], '.sh') !== false ) {							
	sysCmd('/var/www/' . $_GET['cmd']); // BASH
}
elseif (stripos($_GET['cmd'], '.php') !== false ) {							
	sysCmd('/var/www/' . $_GET['cmd']); // PHP 
}
else {
	if (false === ($sock = openMpdSock('localhost', 6600))) {
		debugLog('command/index: Connection to mpd failed');
		echo json_encode(array('error' => 'openMpdSock() failed', 'module' => 'command/index'));
		exit();	
	} 
	else {
		sendMpdCmd($sock, $_GET['cmd']); // mpd command
		echo readMpdResp($sock);
		closeMpdSock($sock);
	}
}

