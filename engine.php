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
 * 2016-04-DD 2.6 TC moodeOS 1.0
 *
 */
 
require_once dirname(__FILE__) . '/inc/playerlib.php';

define(LOGOROOT, 'images/radio-logos/');
define(DEFRADIOCOVER, 'images/radio-cover.jpg');
define(DEFSONGCOVER, 'images/default-cover.jpg');

//debugLog('engine: connect');

$sock = openMpdSock('localhost', 6600);

if (!$sock) {
	$msg = 'engine: connection to mpd failed'; 
	debugLog($msg);
	die($msg . "\n");
}

 // get initial mpd status data
$current = parseStatus(getMpdStatus($sock));

// mpd idle
if ($_GET['state'] == $current['state']) {
	
	// idle mpd and wait for change in state
	sendMpdCmd($sock, 'idle'); 
	$resp = readMpdResp($sock);
	
	// get new status
	$current = parseStatus(getMpdStatus($sock));
	
	// add idle timeout event
	$current['idle_timeout_event'] = explode("\n", $resp)[0];
}

// load session vars (cfg_engine + cfg_radio)
// NOTE cfg_radio vars are loaded into $_SESSION by worker so might not be present here until worker startup completes
playerSession('open', '', '');
session_write_close();

// create enhanced metadata
$current = enhanceMetadata($current, $sock, 'mediainfo');

echo json_encode($current);

closeMpdSock($sock);
