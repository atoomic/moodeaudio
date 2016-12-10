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
 * 2016-08-28 2.7 TC
 * - change from workerLog to debugLog for MPD socket connect fail
 * - check for available software update 
 * - get moode release version and date
 * - get cover art embedded in AIFF format 
 * - wrong encodedAT rate displayed for UPnP files
 * - add crossfeed output in mpd config
 * - add parseMpdOutputs()
 * - add "no setup" to resp check in parseHwParams
 * - upd getMixerName() from statuic PCM to dynamic mixer name
 * - add quotes around amixname
 * - add disableWifiBt()
 * - fix /etc/network/interfaces wlan0 section
 * 2016-11-27 3.0 TC
 * - add DSD128 (705.6 kHz) to formatRate()
 * - improve bit rate formatting in parseStatus()
 * - improve chip option handling
 * - handle adv i2s audio overlays
 * - getDeviceName (for mpd and sqe-config
 * 2016-12-05 3.1 TC squeezelite-armv6l and -armv7l
 *
 */
 
define('MPD_RESPONSE_ERR', 'ACK');
define('MPD_RESPONSE_OK',  'OK');
define('SQLDB', 'sqlite:/var/www/db/player.db');

error_reporting(E_ERROR);

// worker message logger
function workerLog($msg, $mode) {
	if (!isset($mode)) {$mode = 'a';} // default= append mode
		
	$fh = fopen('/var/log/moode.log', $mode);
	fwrite($fh, date('Ymd His ') . $msg . "\n");
	fclose($fh);
}

// debug message logger
function debugLog($msg, $mode) {

	// logging off
	if (!isset($_SESSION['debuglog']) || $_SESSION['debuglog'] == '0') {
		return;
	}

	if (!isset($mode)) {$mode = 'a';} // default= append mode
		
	$fh = fopen('/var/log/moode.log', $mode);
	fwrite($fh, date('Ymd His ') . $msg . "\n");
	fclose($fh);
}

// core mpd functions

// AG from Moode 3 prototype
function openMpdSock($host, $port) {
	if (false === ($sock = @stream_socket_client('tcp://' . $host . ':' . $port, $errorno, $errorstr, 30))) {
		debugLog('openMpdSocket(): could not connect to MPD');
	}
	else {
		$resp = readMpdResp($sock);
	}

	return $sock;
}

// TC rewrite to handle fgets() fail
function readMpdResp($sock) {
	$resp = '';

	while (false !== ($str = fgets($sock, 1024)) && !feof($sock)) {
		if (strncmp(MPD_RESPONSE_OK, $str, strlen(MPD_RESPONSE_OK)) == 0) {
			return $resp;
		}

		if (strncmp(MPD_RESPONSE_ERR, $str, strlen(MPD_RESPONSE_ERR)) == 0) {
			$msg = 'readMpdResponse() error: ' . $str;
			debugLog($msg);
			return $msg;
		}

		$resp .= $str;
	}

	if (!feof($sock)) {
		debugLog('readMpdResponse() error: fgets() fail due to socket being timed out or PHP/MPD connection failure');
		debugLog('readMpdResponse() $resp: (' . $resp . ')');
	}

	return $resp;
}

function closeMpdSock($sock) {
	sendMpdCmd($sock, 'close');
	fclose($sock);
}

function sendMpdCmd($sock, $cmd) {
	fputs($sock, $cmd . "\n");	
}

function chainMpdCmds($sock, $cmds) {
    foreach ($cmds as $cmd) {
        sendMpdCmd($sock, $cmd);
        readMpdResp($sock);
    }
}

function getMpdStatus($sock) {
	sendMpdCmd($sock, 'status');
	$status = readMpdResp($sock);
	
	return $status;
}

// miscellaneous core functions

function sysCmd($cmd) {
	exec('sudo ' . $cmd . " 2>&1", $output);
	return $output;
}

function getTemplate($template) {
	return str_replace("\"", "\\\"", implode("", file($template)));
}

function echoTemplate($template) {
	echo $template;
}

function phpVer() {
	$version = phpversion();
	return substr($version, 0, 3); 
}

if (phpVer() == '5.3') {
	// fix sessions per environment PHP 5.3
	function session_status() {
		if (session_id()) {
			return 1;
		} else {
			return 2;
		}
	}
}

// caching library loader TC and AG
function loadLibrary($sock) {
	if (filesize('/var/www/libcache.json') != 0) {
		debugLog('loadLibrary(): Cache data returned to client');
		return file_get_contents('/var/www/libcache.json');
	} else {
		debugLog('loadLibrary(): Generating flat list...');
		$flat = genFlatList($sock);
				
		if ($flat != '') {
			debugLog('loadLibrary(): Flat list generated');
			debugLog('loadLibrary(): Generating tag cache...');
			$tagcache = json_encode(genLibrary($flat));
			debugLog('loadLibrary(): Cache data returned to client');
			return $tagcache;
		} else {
			debugLog('loadLibrary(): Flat list empty');
			return '';
		}
	}
}

// generate flat list from mpd tag database
function genFlatList($sock) {
	sendMpdCmd($sock, 'find modified-since 2000-01-01T00:00:00Z'); // full time stamp, year 2000
	$resp = readMpdResp($sock);
	
	if (!is_null($resp) && substr($resp, 0, 2) != 'OK') {
		$lines = explode("\n", $resp);
		$item = 0;
		$flat = array();
		
		for ($i = 0; $i < count($lines); $i++) {
			list($element, $value) = explode(': ', $lines[$i], 2);
			if ($element == 'file') {
				$item = count($flat);
			}

			$flat[$item][$element] = $value;
		} 
		
		return $flat;
	} else {
		return '';		
	}
}

// generate library {Genre1: {Artist1: {Album1: [{song1}, {song2}], Album2:...}, Artist2:...}, Genre2:...}
function genLibrary($flat) {
	$lib = array();

	foreach ($flat as $flatData) {
		$genre = $flatData['Genre'] ? $flatData['Genre'] : 'Unknown';
		$artist = $flatData['Artist'] ? $flatData['Artist'] : 'Unknown';
		$album = $flatData['Album'] ? $flatData['Album'] : 'Unknown';

		if (!$lib[$genre]) {$lib[$genre] = array();}
		if (!$lib[$genre][$artist]) {$lib[$genre][$artist] = array();}
        if (!$lib[$genre][$artist][$album]) {$lib[$genre][$artist][$album] = array();}

		$songData = array('file' => $flatData['file'], 'display' => ($flatData['Track'] ? $flatData['Track'] . ' - ' : '') . 
			$flatData['Title'], 'time' => $flatData['Time'], 'time2' => songTime($flatData['Time']));
			
		array_push($lib[$genre][$artist][$album], $songData);
	}
	
	if (file_put_contents('/var/www/libcache.json', json_encode($lib)) === false) {
		debugLog('genLibrary: create libcache.json failed');		
	}
	
	return $lib;
}

// add group of songs to playlist (library panel)
function addallToPL($sock, $songs) {
	$cmds = array();

	foreach ($songs as $song) {
		$path = $song['file'];
		array_push($cmds, 'add "' . html_entity_decode($path) . '"');
	}
	
	chainMpdCmds($sock, $cmds);
}

// add to playlist (browse panel)
function addToPL($sock, $path) {
	$ext = getFileExt($path);

	if ($ext === 'm3u' OR $ext === 'pls' OR $ext === 'cue' OR strpos($path, '/') === false) {

		// for Soma FM stations
		if ($ext === 'pls' && strpos($path, 'Soma FM') !== false) {
			updStreamLink($path);
		}

		sendMpdCmd($sock, 'load "' . html_entity_decode($path) . '"');
	} else {
		sendMpdCmd($sock, 'add "' . html_entity_decode($path) . '"');
	}

	$resp = readMpdResp($sock);
	return $resp;
}

// get file extension
function getFileExt($file) {
	$pos = strrpos($file, '.');
	$ext = substr($file, $pos + 1);
	
	return strtolower($ext);
}

// update direct stream link
function updStreamLink($path) {
	$plsFile = '/var/lib/mpd/music/' . $path;

	// current pls file
	$currentPls = file_get_contents($plsFile);
	$currentUrl = parseDelimFile($currentPls, '=')['File1'];

	// pls permalink file from streaming service provider
	$servicePls = file_get_contents($_SESSION[$currentUrl]['permalink']);

	if ($servicePls !== false) {
		$serviceUrl = parseDelimFile($servicePls, '=')['File1'];
	
		if ($currentUrl != $serviceUrl) {
			// update pls file and sql table
			$currentPls = str_replace($currentUrl, $serviceUrl, $currentPls);
			file_put_contents($plsFile, $currentPls);
			cfgdb_update('cfg_radio', cfgdb_connect(), $_SESSION[$currentUrl]['name'], $serviceUrl);
	
			// create new session var
			session_start();
			$_SESSION[$serviceUrl] = $_SESSION[$currentUrl];
			unset($_SESSION[$currentUrl]);
			session_write_close();
		}
	}
}

// parse delimited file
function parseDelimFile($data, $delim) {
	$array = array();
	$line = strtok($data, "\n");
	
	while ($line) {
		list($param, $value) = explode($delim, $line, 2);
		$array[$param] = $value;
		$line = strtok("\n");
	}

	return $array;
}

// get playist
function getPLInfo($sock) {
	sendMpdCmd($sock, 'playlistinfo');
	$resp = readMpdResp($sock);
	
	$pl = parseList($resp);
	
	return $pl;
}

// list contents of saved playlist
function listSavedPL($sock, $plname) {
	sendMpdCmd($sock, 'listplaylist "' . $plname . '"');
	$pl = readMpdResp($sock);
	
	return parseList($pl);
}

// delete saved playlist file
function delPLFile($sock, $plname) {
	sendMpdCmd($sock, 'rm "' . $plname . '"');
	$resp = readMpdResp($sock);
	return $resp;
}

// search mpd database
function searchDB($sock, $querytype, $query) {
	switch ($querytype) {
		case 'filepath':
			if (isset($query) && !empty($query)){
				sendMpdCmd($sock, 'lsinfo "' . html_entity_decode($query) . '"');
				break;
			} else {
				sendMpdCmd($sock, 'lsinfo');
				break;
			}
		case 'album':
		case 'artist':
		case 'title':
		case 'file':
			sendMpdCmd($sock, 'search ' . $querytype . ' "' . html_entity_decode($query) . '"');
			break;	
	}	

	$resp = readMpdResp($sock);
	return parseList($resp);
}

// format mpd list output
function parseList($resp) {
	if (is_null($resp)) {
		return NULL;
	} else {
		$array = array();
		$line = strtok($resp,"\n");
		$file = '';
		$idx = -1;

		while ($line) {
			list ($element, $value) = explode(': ', $line, 2);

			if ($element == 'file') {
				$idx++;
				$file = $value;
				$array[$idx]['file'] = $file;
				$array[$idx]['fileext'] = getFileExt($file);
			} else if ($element == 'directory') {
				$idx++;				
				$diridx++; // record directory index for further processing
				$file = $value;
				$array[$idx]['directory'] = $file;
			} else if ($element == 'playlist') {
				if (substr($value,0, 5) == 'RADIO' || strtolower(pathinfo($value, PATHINFO_EXTENSION)) == 'cue') {
					$idx++;
					$file = $value;
					$array[$idx]['file'] = $file;
					$array[$idx]['fileext'] = getFileExt($file);
				} else {
					$idx++;
					$file = $value;
					$array[$idx]['playlist'] = $file;
				}
			} else {
				$array[$idx][$element] = $value;
				$array[$idx]['Time2'] = songTime($array[$idx]['Time']);
			}

			$line = strtok("\n");
		}
		
		// reverse list output
		if (isset($diridx) && isset($array[0]['file']) ) {
			$dir = array_splice($array, -$diridx);
			$array = $dir + $array;
		}
	}
	
	return $array;
}

function songTime($sec) {
	$mins = sprintf('%02d', floor($sec / 60));
	$secs = sprintf(':%02d', (int) $sec % 60);
	
	return $mins . $secs;
}

// format mpd status output
function parseStatus($resp) {
	if (is_null($resp)) {
		return NULL;
	} else {
		$array = array();
		$line = strtok($resp, "\n");
		
		while ($line) {
			list($element, $value) = explode(': ', $line, 2);
			$array[$element] = $value;
			$line = strtok("\n");
		} 

		// elapsed time
		$time = explode(':', $array['time']);
		
		if ($time[0] != 0) {
			$percent = round(($time[0] * 100) / $time[1]);	
		} else {
			$percent = 0;
		}
		
		$array['song_percent'] = $percent;
		$array['elapsed'] = $time[0];
		$array['time'] = $time[1];

		 // sample rate
	 	$audio_format = explode(':', $array['audio']);
	 	$array['audio_sample_rate'] = formatRate($audio_format[0]);

		// bit depth
		// workaround for AAC files that show "f" for bit depth, assume decoded to 24 bit
	 	$array['audio_sample_depth'] = $audio_format[1] == 'f' ? '24' : $audio_format[1];
	 	
	 	// channels
	 	$array['audio_channels'] = formatChan($audio_format[2]);

		// bit rate
		if (!isset($array['bitrate']) || trim($array['bitrate']) == '') {
			$array['bitrate'] = '0 bps';
		}
	 	else {
		 	// for aiff, wav files and some radio stations ex: Czech Radio Classic
			if ($array['bitrate'] == '0') {
			 	$array['bitrate'] = number_format((( (float)$audio_format[0] * (float)$array['audio_sample_depth'] * (float)$audio_format[2] ) / 1000000), 3, '.', '');
			}
			else {
			 	$array['bitrate'] = strlen($array['bitrate']) < 4 ? $array['bitrate'] : substr($array['bitrate'], 0, 1) . '.' . substr($array['bitrate'], 1, 3) ;
			}

			$array['bitrate'] .= strpos($array['bitrate'], '.') === false ? ' kbps' : ' mbps';
		}
	}

	return $array;
}

function formatRate ($rate) {
	$rates = array('*' => '*', '32000' => '32', '48000' => '48', '96000' => '96', '192000' => '192', '384000' => '384', 
	'22050' => '22.05', '44100' => '44.1', '88200' => '88.2', '176400' => '176.4', '352800' => '352.8', '705600' => '705.6');

	return $rates[$rate];
}

function formatChan($channels) {
	if ($channels == '1') {
	 	$chanStr = 'Mono';
	} else if ($channels == '2' || $channels == '*') {
	 	$chanStr = 'Stereo';
	} else if ($channels > 2) {
	 	$chanStr = 'Multichannel';
	}

 	return $chanStr;
}

// parse audio output hardware params
function parseHwParams($resp) {
	if (is_null($resp)) {
		return 'Error, parseHwParams response is null';
	}
	elseif ($resp != "closed\n" && $resp != "no setup\n") {
		$array = array();
		$line = strtok($resp, "\n");
		
		while ($line) {
			list ( $element, $value ) = explode(": ", $line);
			$array[$element] = $value;
			$line = strtok("\n");
		} 
		
		// rate "44100 (44100/1)"
	 	$rate = substr($array['rate'], 0, strpos($array['rate'], ' ('));
	 	$array['rate'] = formatRate($rate);
	 	$_rate = (float)$rate;
	 	
		// bits "S24_3LE"
		$array['format'] = substr($array['format'], 1, 2);
		$_bits = (float)$array['format'];
		
		// channels
		$_chans = (float)$array['channels'];
		$array['channels'] = formatChan($array['channels']);

		$array['status'] = 'active';
		$array['calcrate'] = number_format((($_rate * $_bits * $_chans) / 1000000), 3, '.', '');	 
	}
	else {		
		$array['status'] = trim($resp, "\n");
		$array['calcrate'] = '0 bps';	 
	}
	
	return $array;
}

// parse mpd currentsong output
function parseCurrentSong($sock) {
	sendMpdCmd($sock, 'currentsong');
	$resp = readMpdResp($sock);

	if (is_null($resp) ) {
		return 'Error, parseCurrentSong response is null';
	} else {
		$array = array();
		$line = strtok($resp, "\n");

		while ($line) {
			list ($element, $value) = explode(": ", $line, 2);
			$array[$element] = $value;
			$line = strtok("\n");
		}
		
		return $array;
	}
}

// parse mpd conf settings
function parseCfgMpd($dbh) {
	// load settings
	$result = cfgdb_read('cfg_mpd', $dbh);
	$array = array();
	
	foreach ($result as $row) {
		$array[$row['param']] = $row['value_player'];
	}
	
	// ex 44100:16:2 or disabled
	if ($array['audio_output_format'] == 'disabled') {
	 	$array['audio_output_rate'] = '';
	 	$array['audio_output_depth'] = '';
	 	$array['audio_output_chan'] = '';
	} else {
	 	$format = explode(":", $array['audio_output_format']);
	 	$array['audio_output_rate'] = formatRate($format[0]);
	 	$array['audio_output_depth'] = $format[1];
	 	$array['audio_output_chan'] = formatChan($format[2]);
	}
	
	return $array;
}
	
// parse radio station file
function parseStationFile($resp) {
	if (is_null($resp) ) {
		return 'Error, parseStationFile response is null';
	} else {
		$array = array();
		$line = strtok($resp, "\n");

		while ($line) {
			list ($element, $value) = explode("=", $line, 2);
			$array[$element] = $value;
			$line = strtok("\n");
		} 
	}
		
	return $array;
}
	
// parse play history log
function parsePlayHist($resp) {
	if (is_null($resp) ) {
		return 'Error, parsePlayHist response is null';
	} else {
		$array = array();
		$line = strtok($resp, "\n");
		$i = 0;
		
		while ( $line ) {
			$array[$i] = $line;
			$i++;
			$line = strtok("\n");
		} 
	}

	return $array;
}

// update play history log
function updPlayHist($historyitem) {
	$file = '/var/www/playhistory.log';
	$fh = fopen($file, 'a') or die('moode.php: file open failed on ' . $file);
	fwrite($fh, $historyitem . "\n");
	fclose($fh);
	
	return 'OK';
}
	
// session and sql table management
function playerSession($action, $var, $value) {
	$status = session_status();	

	// open session
	if ($action == 'open') {		
		if($status != 2) { // 2 = active session
			$sessionid = playerSession('getsessionid'); // session not active so get from sql
			if (!empty($sessionid)) {
				session_id($sessionid); // set session to existing id
				session_start();
			} else {
				session_start();
				playerSession('storesessionid'); // store new session id
			}
		}
	
		// load cfg_engine sql table into session vars
		$dbh  = cfgdb_connect();
		$params = cfgdb_read('cfg_engine', $dbh);

		foreach ($params as $row) {
			$_SESSION[$row['param']] = $row['value'];
		}
		
		$dbh  = null;
	}

	// unlock session files
	if ($action == 'unlock') {
		session_write_close();
	}
	
	// unset and destroy session
	if ($action == 'destroy') {
		session_unset();
		
		if (session_destroy()) {
			$dbh  = cfgdb_connect();
			
			// clear the session id 
			if (cfgdb_update('cfg_engine', $dbh, 'sessionid','')) {
				$dbh = null;
				return true;
			} else {
				echo "cannot reset session on SQLite datastore";
				return false;
			}
		}
	}
	
	// store a value in the cfgdb and session var
	if ($action == 'write') {
		$_SESSION[$var] = $value;
		$dbh  = cfgdb_connect();
		cfgdb_update('cfg_engine', $dbh, $var, $value);
		$dbh = null;
	}
	
	// store session id
	if ($action == 'storesessionid') {
		$sessionid = session_id();
		playerSession('write', 'sessionid', $sessionid);
	}
	
	// get session id from sql (used in worker)
	if ($action == 'getsessionid') {
		$dbh  = cfgdb_connect();
		$result = cfgdb_read('cfg_engine', $dbh, 'sessionid');
		$dbh = null;

		return $result['0']['value'];
	}
}

function cfgdb_connect() {
	if ($dbh  = new PDO(SQLDB)) {
		return $dbh;
	} else {
		echo "cannot open SQLite database";
		return false;
	}
}

function cfgdb_read($table, $dbh, $param, $id) {
	if(!isset($param)) {
		$querystr = 'SELECT * from ' . $table;

	} else if (isset($id)) {
		$querystr = "SELECT * from " . $table . " WHERE id='" . $id . "'";

	} else if ($param == 'mpdconf') {
		$querystr = "SELECT param, value_player FROM cfg_mpd WHERE value_player!=''";

	} else if ($param == 'mpdconfdefault') {
		$querystr = "SELECT param, value_default FROM cfg_mpd WHERE value_default!=''";

	} else if ($table == 'cfg_audiodev') {
		$querystr = 'SELECT name, dacchip, arch, iface, driver, advdriver, settings from ' . $table . ' WHERE name="' . $param . '"';

	} else if ($table == 'cfg_radio') {
		$querystr = 'SELECT station, name, logo from ' . $table . ' WHERE station="' . $param . '"';

	} else {
		$querystr = 'SELECT value from ' . $table . ' WHERE param="' . $param . '"';
	}

	$result = sdbquery($querystr, $dbh);
	return $result;
}

function cfgdb_update($table, $dbh, $key, $value) {
	switch ($table) {
		case 'cfg_engine':
			$querystr = "UPDATE " . $table . " SET value='" . $value . "' where param='" . $key . "'";
			break;
		
		case 'cfg_mpd':
			$querystr = "UPDATE " . $table . " set value_player='" . $value . "' where param='" . $key . "'";
			break;
		
		case 'cfg_network':
			// use escaped single quotes in ssid and pwd
			$querystr = "update " . $table . 
				" set method='" . $value['method'] . 
				"', ipaddr='" . $value['ipaddr'] . 
				"', netmask='" . $value['netmask'] . 
				"', gateway='" . $value['gateway'] . 
				"', pridns='" . $value['pridns'] . 
				"', secdns='" . $value['secdns'] . 
				"', wlanssid='" . str_replace("'", "''", $value['wlanssid']) . 
				"', wlansec='" . $value['wlansec'] . 
				"', wlanpwd='" . str_replace("'", "''", $value['wlanpwd']) . 
				"' where iface='" . $key . "'";
				
			//debugLog('cfgdb_update: ' . $querystr);
			break;
		
		case 'cfg_source':
			$querystr = "UPDATE " . $table . " SET name='" . $value['name'] . "', type='" . $value['type'] . "', address='" . $value['address'] . "', remotedir='" . $value['remotedir'] . "', username='" . $value['username'] . "', password='" . $value['password'] . "', charset='" . $value['charset'] . "', rsize='" . $value['rsize'] . "', wsize='" . $value['wsize'] . "', options='" . $value['options'] . "', error='" . $value['error'] . "' where id=" . $value['id'];
			break;
		
		case 'cfg_audiodev':
			$querystr = "UPDATE " . $table . " SET settings='" . $value . "' where name='" . $key . "'";
			break;
		
		case 'cfg_radio':
			$querystr = "UPDATE " . $table . " SET station='" . $value . "' where name='" . $key . "'";
			break;
		case 'cfg_sl':
			$querystr = "UPDATE " . $table . " SET value='" . $value . "' where param='" . $key . "'";
			break;
	}

	if (sdbquery($querystr,$dbh)) {
		return true;
	} else {
		return false;
	}
}

function cfgdb_write($table, $dbh, $values) {
	$querystr = "INSERT INTO " . $table . " VALUES (NULL, " . $values . ")";

	if (sdbquery($querystr,$dbh)) {
		return true;
	} else {
		return false;
	}
}

function cfgdb_delete($table, $dbh, $id) {
	if (!isset($id)) {
		$querystr = "DELETE FROM " . $table;
	} else {
		$querystr = "DELETE FROM " . $table . " WHERE id=" . $id;
	}

	if (sdbquery($querystr,$dbh)) {
		return true;
	} else {
		return false;
	}
}

function sdbquery($querystr, $dbh) {
	$query = $dbh->prepare($querystr);
	if ($query->execute()) {
		$result = array();
		$i = 0;
		foreach ($query as $value) {
			$result[$i] = $value;
			$i++;
		}
		$dbh = null;
		if (empty($result)) {
			return true;
		} else {
			return $result;
		}
	} else {
		return false;
	}
}

function wrk_mpdconf($i2sdevice) {
	// load settings
	$dbh = cfgdb_connect();
	$query_cfg = "SELECT param,value_player FROM cfg_mpd WHERE value_player!=''";
	$mpdcfg = sdbquery($query_cfg, $dbh);
	
	// header
	$output =  "#########################################\n";
	$output .= "# This file is automatically generated by\n";
	$output .= "# the player MPD configuration page.     \n";
	$output .= "#########################################\n\n";
	
	// parse output
	foreach ($mpdcfg as $cfg) {
		if ($cfg['param'] == 'audio_output_format' && $cfg['value_player'] == 'disabled') {
			$output .= '';
		} else if ($cfg['param'] == 'dsd_usb') {
			$dsd = $cfg['value_player'];
		} else if ($cfg['param'] == 'device') {
			$device = $cfg['value_player'];
		} else if ($cfg['param'] == 'mixer_type') {
			playerSession('write', 'mpdmixer', $cfg['value_player']);
			if ($cfg['value_player'] == 'hardware') { 
				$hwmixer = getMixerName($i2sdevice);
			} else {
				$output .= $cfg['param'] . " \"" . $cfg['value_player'] . "\"\n";
			}
		// turn off logging	
		#} else if ($cfg['param'] == 'log_file') {
		#	$output .= "#".$cfg['param'] . " \"" . $cfg['value_player'] . "\"\n";
		} else {
			$output .= $cfg['param'] . " \"" . $cfg['value_player'] . "\"\n";
		}
	}

	// format audio input / output interfaces
	$output .= "max_connections \"20\"\n";
	$output .= "\n";
	$output .= "decoder {\n";
	$output .= "plugin \"ffmpeg\"\n";
	$output .= "enabled \"yes\"\n";
	$output .= "}\n";
	$output .= "\n";
	$output .= "input {\n";
	$output .= "plugin \"curl\"\n";
	$output .= "}\n";
	$output .= "\n";
	// ALSA default output
	$output .= "audio_output {\n";
	$output .= "type \"alsa\"\n";
	$output .= "name \"ALSA default\"\n";
	$output .= "device \"hw:" . $device . ",0\"\n";
	if (isset($hwmixer)) {
		$output .= "mixer_control \"" . $hwmixer . "\"\n";
		$output .= "mixer_device \"hw:" . $device . "\"\n";
		$output .= "mixer_index \"0\"\n";
	}
	$output .= "dsd_usb \"" . $dsd . "\"\n";
	$output .= "}\n\n";
	// ALSA crossfeed output
	$output .= "audio_output {\n";
	$output .= "type \"alsa\"\n";
	$output .= "name \"ALSA crossfeed\"\n";
	$output .= "device \"crossfeed\"\n";
	if (isset($hwmixer)) {
		$output .= "mixer_control \"" . $hwmixer . "\"\n";
		$output .= "mixer_device \"hw:" . $device . "\"\n";
		$output .= "mixer_index \"0\"\n";
	}
	$output .= "dsd_usb \"" . $dsd . "\"\n";
	$output .= "}\n";

	$fh = fopen('/etc/mpd.conf', 'w');
	fwrite($fh, $output);
	fclose($fh);

	// update crossfeed config with device#
	sysCmd("sed -i '/slave.pcm \"plughw/c\ \tslave.pcm \"plughw:" . $device . ",0\";' /usr/share/alsa/alsa.conf.d/crossfeed.conf");
}

// return amixer name
function getMixerName($i2sdevice) {
	if ($i2sdevice == 'none') {
		// USB mixer name from ALSA (default is PCM but could be other name)
		$result = sysCmd('/var/www/command/util.sh get-mixername');
		$mixername = $result[0];
	}
	elseif ($i2sdevice == 'HiFiBerry Amp(Amp+)') {
		$mixername = 'Master';
	}
	else {
		 // I2S default
		$mixername = 'Digital';
	}

	return $mixername;
}

// make text for audio device field (mpd and sqe-config)
function getDeviceNames () {
	$dev = array();

	$card0 = file_get_contents('/proc/asound/card0/id');
	$card1 = file_get_contents('/proc/asound/card1/id');
	
	// device 0
	if ($card0 == "ALSA\n") {
		$dev[0] = 'On-board audio device';
	} 
	else if ($_SESSION['i2sdevice'] != 'none') {
		$dev[0] = 'I2S audio device';
	}
	else {
		$dev[0] = '';
	}
	
	// device 1
	if ($card1 != '' && $card0 == "ALSA\n") {
		$dev[1] = 'USB audio device';
	}
	else {
		$dev[1] = '';
	}

	return $dev;
}

function wrk_sourcecfg($queueargs) {
	$action = $queueargs['mount']['action'];
	unset($queueargs['mount']['action']);
	
	switch ($action) {
		case 'add':
			$dbh = cfgdb_connect();
			print_r($queueargs);
			unset($queueargs['mount']['id']);
			
			// format values string
			foreach ($queueargs['mount'] as $key => $value) {
				if ($key == 'error') {
					$values .= "'" . SQLite3::escapeString($value) . "'";
					//error_log("wrk_sourcecfg() error= " . $values, 0);
				} else {
					$values .= "'" . SQLite3::escapeString($value) . "',";
					//error_log("wrk_sourcecfg() mount= " . $values, 0);
				}
			}

			// write new entry
			cfgdb_write('cfg_source', $dbh, $values);
			$newmountID = $dbh->lastInsertId();
			$dbh = null;
			
			if (wrk_sourcemount('mount', $newmountID)) {
				$return = 1;
			} else {
				$return = 0;
			}
			
			break;
		
		case 'edit':
			$dbh = cfgdb_connect();
			$mp = cfgdb_read('cfg_source', $dbh, '', $queueargs['mount']['id']);
			
			cfgdb_update('cfg_source', $dbh, '', $queueargs['mount']);
			
			if ($mp[0]['type'] == 'cifs') {
				sysCmd('umount -l "/mnt/NAS/' . $mp[0]['name'] . '"'); // cifs lazy unmount
			} else {
				sysCmd('umount -f "/mnt/NAS/' . $mp[0]['name'] . '"'); // nfs force unmount
			}
			
			if ($mp[0]['name'] != $queueargs['mount']['name']) {
				sysCmd('rmdir "/mnt/NAS/' . $mp[0]['name'] . '"');
				sysCmd('mkdir "/mnt/NAS/' . $queueargs['mount']['name'] . '"');
			}
			
			if (wrk_sourcemount('mount', $queueargs['mount']['id'])) {
				$return = 1;
			} else {
				$return = 0;
			}

			$dbh = null;
			
			break;
		
		case 'delete':
			$dbh = cfgdb_connect();
			$mp = cfgdb_read('cfg_source', $dbh, '', $queueargs['mount']['id']);
			
			if ($mp[0]['type'] == 'cifs') {
				sysCmd('umount -l "/mnt/NAS/' . $mp[0]['name'] . '"'); // cifs lazy unmount
			} else {
				sysCmd('umount -f "/mnt/NAS/' . $mp[0]['name'] . '"'); // nfs force unmount
			}

			sysCmd('rmdir "/mnt/NAS/' . $mp[0]['name'] . '"');

			if (cfgdb_delete('cfg_source', $dbh, $queueargs['mount']['id'])) {
				$return = 1;
			} else {
				$return = 0;
			}

			$dbh = null;

			break;
	}

	return $return;
}

function wrk_sourcemount($action, $id) {
	switch ($action) {
		case 'mount':
			$dbh = cfgdb_connect();
			$mp = cfgdb_read('cfg_source', $dbh, '', $id);

			sysCmd("mkdir \"/mnt/NAS/" . $mp[0]['name'] . "\"");

			if ($mp[0]['type'] == 'cifs') {
				// smb/cifs mount
				$mountstr = "mount -t cifs \"//" . $mp[0]['address'] . "/" . $mp[0]['remotedir'] . "\" -o username=" . $mp[0]['username'] . ",password='" . $mp[0]['password'] . "',rsize=" . $mp[0]['rsize'] . ",wsize=" . $mp[0]['wsize'] . ",iocharset=" . $mp[0]['charset'] . "," . $mp[0]['options'] . " \"/mnt/NAS/" . $mp[0]['name'] . "\"";
			} else {
				// nfs mount
				$mountstr = "mount -t nfs -o " . $mp[0]['options'] . " \"" . $mp[0]['address'] . ":/" . $mp[0]['remotedir'] . "\" \"/mnt/NAS/" . $mp[0]['name'] . "\"";
			}

			$sysoutput = sysCmd($mountstr);

			if (empty($sysoutput)) {
				if (!empty($mp[0]['error'])) {
					$mp[0]['error'] = '';
					cfgdb_update('cfg_source', $dbh, '', $mp[0]);
				}

				$return = 1;
			} else {
				sysCmd("rmdir \"/mnt/NAS/" . $mp[0]['name'] . "\"");
				$mp[0]['error'] = implode("\n", $sysoutput);
				cfgdb_update('cfg_source', $dbh, '', $mp[0]);

				$return = 0;
			}	

			break;
		
		case 'mountall':
			$dbh = cfgdb_connect();

			// cfgdb_read returns: query results === true if results empty | false if query failed
			$mounts = cfgdb_read('cfg_source', $dbh);
			//debugLog('wrk_sourcemount(): $mounts= <' . $mounts . '>');

			foreach ($mounts as $mp) {
				if (!wrk_checkStrSysfile('/proc/mounts', $mp['name']) ) {
					$return = wrk_sourcemount('mount', $mp['id']);
				}
			}

			// status returned to worker
			if ($mounts === true) {
				$return = 'none configured';
			} else if ($mounts === false) {
				$return = 'query failed';
			} else {
				$return = 'mountall initiated';
			}

			break;
	}

	return $return;
}

function ui_notify($notify) {
	$output .= "<script>";
	$output .= "jQuery(document).ready(function() {";
	$output .= "$.pnotify.defaults.history = false;";
	$output .= "$.pnotify({";
	$output .= "title: '" . $notify['title'] . "',";
	$output .= "text: '" . $notify['msg'] . "',";
	$output .= "icon: 'icon-ok',";
	if (isset($notify['duration'])) {	
		$output .= "delay: " . strval($notify['duration'] * 1000) . ",";
	} else {
		$output .= "delay: '2000',";
	}
	$output .= "opacity: .9});";
	$output .= "});";
	$output .= "</script>";
	echo $output;
}

function waitWorker($sleeptime, $caller) {
	debugLog('waitWorker(): Start (' . $caller . ', w_active=' . $_SESSION['w_active'] . ')');
	$loopcnt = 0;

	if ($_SESSION['w_active'] == 1) {
		do {
			sleep($sleeptime);
			session_start();
			session_write_close();

			debugLog('waitWorker(): Wait  (' . ++$loopcnt . ')');

		} while ($_SESSION['w_active'] != 0);

		// initiate mpd db update 
		if ($caller == 'src-config' ) {
			$sock = openMpdSock('localhost', 6600);
			sendMpdCmd($sock, 'update');
			closeMpdSock($sock);
		}
	}

	debugLog('waitWorker(): End   (' . $caller . ', w_active=' . $_SESSION['w_active'] . ')');
} 

function wrk_checkStrSysfile($sysfile, $searchstr) {
	$file = stripcslashes(file_get_contents($sysfile));
	if (strpos($file, $searchstr)) {
		return true;
	} else {
		return false;
	}
}

// return kernel version without "-v7" suffix
function getKernelVer($kernel) {
	return str_replace('-v7', '', $kernel);
}

// submit job to worker.php
function submitJob($jobName, $jobArgs, $title, $msg, $duration) {
	if ($_SESSION['w_lock'] != 1 && $_SESSION['w_queue'] == '') {
		session_start();
		
		$_SESSION['w_queue'] = $jobName;
		$_SESSION['w_active'] = 1;
		$_SESSION['w_queueargs'] = $jobArgs;
		
		// we do it this way because $_SESSION['notify'] is tested in footer.php and jobs can be submitted by js
		if ($title !== '') {$_SESSION['notify']['title'] = $title;}
		if ($msg !== '') {$_SESSION['notify']['msg'] = $msg;}
		if (isset($duration)) {$_SESSION['notify']['duration'] = $duration;}
		
		session_write_close();
		return true;
	} else {
		echo 'worker busy';
		return false;
	}
}

// extract "Audio" metadata from file and format it for display
function getEncodedAt($song, $format) {
	// format 'verbose' = 16 bit, 44.1 kHz, Stereo
	// format 'default' = 16/44.1

	$encoded = '';

	// radio station
	if (isset($song['Name']) || (substr($song['file'], 0, 4) == 'http' && !isset($song['Artist']))) {
		$encoded = $format === 'verbose' ? 'VBR compression' : 'VBR';
	}
	// UPnP file
	elseif (substr($song['file'], 0, 4) == 'http' && isset($song['Artist'])) {
		$encoded = 'Unknown';
	} 
	// DSD file
	elseif (getFileExt($song['file']) == 'dsf' || getFileExt($song['file']) == 'dff') {
		$encoded = 'DSD';
	} 
	// file
	else {
		$fullpath = '/var/lib/mpd/music/' . $song['file'];

		if ($song['file'] == '' || !file_exists($fullpath)) {
			return 'File does not exist';
		}

		$result = sysCmd('mediainfo --Inform="Audio;file:///var/www/mediainfo.tpl" ' . '"' . $fullpath . '"');

		if($result[0] == '') {
			$encoded = 'Unknown';
		} else if ($format === 'verbose') {
			$encoded = $result[0] . ' bit, ' . formatRate($result[1]) . ' kHz, ' . formatChan($result[2]);
		} else {
			$encoded = $result[0] . '/' . formatRate($result[1]);
		}
	}	

	return $encoded;
}

// start shairport-sync
function startSps() {
	// get device num and hardware mixer name
	$array = sdbquery('select value_player from cfg_mpd where param="device"', cfgdb_connect());
	$device = $array[0]['value_player'];
	$mixername = $_SESSION['amixname'];

	// specify whether to make metadata available
	if ($_SESSION['airplaymeta'] == '1') {
		$metadata = '--metadata-pipename=/tmp/shairport-sync-metadata --get-coverart ';
	}
	else {
		$metadata = '';
	}
	debugLog('worker: Airplay metadata ' . ($_SESSION['airplaymeta'] == '1' ? 'on' : 'off'));

	// format cmd string
	$cmd = '/usr/local/bin/shairport-sync -a "' . $_SESSION['airplayname'] . '" -S soxr -w -B /var/www/command/spspre.sh -E /var/www/command/spspost.sh ' . $metadata . '-- -d hw:' . $device;
	if ($_SESSION['airplayvol'] == 'auto') {
		$cmd .= $_SESSION['alsavolume'] == 'none' ? ' > /dev/null 2>&1 &' : ' -c ' . '"' . $mixername  . '"' . ' > /dev/null 2>&1 &';
	}
	else {
		$cmd .= ' > /dev/null 2>&1 &';
	}
	
	// start shairport-sync
	debugLog('worker: (' . $cmd . ')');
	sysCmd($cmd);
}

// initialize shairport-sync metadata cache
function initSpsCache() {
	sysCmd("echo '{\"state\":\"ok\",\"artist\":null,\"album\":null,\"title\":null,\"genre\":null,\"progress\":null,\"volume\":null,\"imgtype\":null,\"imglen\":null,\"imgurl\":null}' > /var/www/spscache.json");
}

// start dlna server
function startMiniDlna() {
	sysCmd('systemctl start minidlna');
	sysCmd('djmount -o allow_other,nonempty,iocharset=utf-8 /mnt/UPNP > /dev/null 2>&1 &');
}

// start lcd updater
function startLcdUpdater() {
	$script = $_SESSION['lcdupscript'] != '' ? $_SESSION['lcdupscript'] : 'cp /var/www/currentsong.txt /home/pi/lcd.txt';
	$cmd = '/var/www/command/lcdup.sh ' . '"' . $script . '"';
	sysCmd($cmd);
}

// get upnp coverart url
function getUpnpCoverUrl() {
	$result = sysCmd('upexplorer --album-art "' . $_SESSION['upnpname'] . '"');
	return $result[0];
}

// configure PCM512x/TAS5756 chip options
function cfgPcm512x($settings) {
	$array = explode(',', $settings);

	sysCmd('amixer -c 0 sset "Analogue" ' . $array[0]); // Analog volume
	sysCmd('amixer -c 0 sset "Analogue Playback Boost" ' . $array[1]); // Analog Playback Boost
	sysCmd('amixer -c 0 sset "DSP Program" ' . '"' . $array[2] . '"'); // Digital interpolation filter
}

// configure network interfaces
function cfgNetIfaces() {
	// get network config
	$result = sdbquery('select * from cfg_network', cfgdb_connect());

	// write new interfaces file
	$file = '/etc/network/interfaces';
	$fp = fopen($file, 'w');

	// header
	$data  = "#########################################\n";
	$data .= "# This file is automatically generated by\n";
	$data .= "# the player Network configuration page. \n";
	$data .= "#########################################\n\n";

	// loopback
	$data .= "auto lo\n";
	$data .= "iface lo inet loopback\n";

	// eth0
	$data .= "\nallow-hotplug eth0\n";
	$data .= 'iface eth0 inet ' . $result[0]['method'] . "\n";
	// static
	if ($result[0]['method'] == 'static') {
		$data .= 'address ' . $result[0]['ipaddr'] . "\n";
		$data .= 'netmask ' . $result[0]['netmask'] . "\n";
		$data .= 'gateway ' . $result[0]['gateway'] . "\n";
		$data .= 'dns-nameservers ' . $result[0]['pridns'] . ' ' . $result[0]['secdns'] . "\n";
	}

	// wlan0
	$data .= "\nallow-hotplug wlan0\n"; // Nigel
	$data .= 'iface wlan0 inet ' . $result[1]['method'] . "\n";
	$data .= "wireless-power off\n";

	if (!empty($result[1]['wlanssid'])) {
		// security
		if ($result[1]['wlansec'] == 'wpa') {
			$data .= 'wpa-ssid ' . '"' . $result[1]['wlanssid'] . '"' . "\n"; // ssid and pwd must be quoted
			$data .= 'wpa-psk ' . '"' . $result[1]['wlanpwd'] . '"' . "\n";
		}
		else {
			// no security
			$data .= 'wireless-essid ' . $result[1]['wlanssid'] . "\n"; // no quotes or connection fail (MrEngman)
			$data .= "wireless-mode managed\n";
		}
		// static
		if ($result[1]['method'] == 'static') {
			$data .= 'address ' . $result[1]['ipaddr'] . "\n";
			$data .= 'netmask ' . $result[1]['netmask'] . "\n";
			$data .= 'gateway ' . $result[1]['gateway'] . "\n";
			$data .= 'dns-nameservers ' . $result[1]['pridns'] . ' ' . $result[1]['secdns'] . "\n";
		}
	}

	// configure dhcpcd conf
	sysCmd('sed -i "/denyinterfaces/c\#denyinterfaces" /etc/dhcpcd.conf');
	if ($result[0]['method'] == 'static' && $result[1]['method'] == 'static' && !empty($result[1]['wlanssid'])) {
		sysCmd('sed -i "s/#denyinterfaces/denyinterfaces eth0, wlan0/" /etc/dhcpcd.conf');
	}
	else if ($result[0]['method'] == 'static') {
		sysCmd('sed -i "s/#denyinterfaces/denyinterfaces eth0/" /etc/dhcpcd.conf');
	}
	else if ($result[1]['method'] == 'static' && !empty($result[1]['wlanssid'])) {
		sysCmd('sed -i "s/#denyinterfaces/denyinterfaces wlan0/" /etc/dhcpcd.conf');
	}

	fwrite($fp, $data);
	fclose($fp);
}

// configure hostapd conf
function cfgHostApd() {
	$file = '/etc/hostapd/hostapd.conf';
	$fp = fopen($file, 'w');

	// header
	$data  = "#########################################\n";
	$data .= "# This file is automatically generated by\n";
	$data .= "# the player Network configuration page. \n";
	$data .= "#########################################\n\n";

	$data .= "# Interface and driver\n";
	$data .= "interface=wlan0\n";
	$data .= "driver=nl80211\n\n";
	
	$data .= "# Wireless settings\n";
	$data .= "ssid=" . $_SESSION['apdssid'] . "\n";
	$data .= "hw_mode=g\n";
	$data .= "channel=" . $_SESSION['apdchan'] . "\n\n";
	
	$data .= "# Security settings\n";
	$data .= "macaddr_acl=0\n";
	$data .= "auth_algs=1\n";
	$data .= "ignore_broadcast_ssid=0\n";
	$data .= "wpa=2\n";
	$data .= "wpa_key_mgmt=WPA-PSK\n";
	$data .= "wpa_passphrase=" . $_SESSION['apdpwd'] . "\n";
	$data .= "rsn_pairwise=CCMP\n";

	fwrite($fp, $data);
	fclose($fp);
}

// return hardware revision
function getHdwrRev() {
	$revname = array(
		'0002' => 'Pi-1B 256MB',	
		'0003' => 'Pi-1B 256MB',
		'0004' => 'Pi-1B 256MB',
		'0005' => 'Pi-1B 256MB',
		'0006' => 'Pi-1B 256MB',
		'0007' => 'Pi-1A 256MB',
		'0008' => 'Pi-1A 256MB',
		'0009' => 'Pi-1A 256MB',
		'000d' => 'Pi-1B 512MB',
		'000e' => 'Pi-1B 512MB',
		'000f' => 'Pi-1B 512MB',
		'0010' => 'Pi-1B+ 512MB',
		'0011' => 'Pi-Compute Module 512MB',
		'0012' => 'Pi-1A+ 256MB',
		'0013' => 'Pi-1B+ 512MB',
		'1041' => 'Pi-2B 1GB',
		'1041' => 'Pi-2B 1GB',
		'0092' => 'Pi-Zero 512MB',
		'2082' => 'Pi-3B 1GB'
	); // a01041, a21041, 900092, 900092

	$revnum = sysCmd('awk ' . "'" . '{if ($1=="Revision") print substr($3,length($3)-3)}' . "'" . ' /proc/cpuinfo');

	return array_key_exists($revnum[0], $revname) ? $revname[$revnum[0]] : 'Unknown Pi-model';
}

// config audio scrobbler
function cfgAudioScrobbler($cfg) {
	$file = '/usr/local/etc/mpdasrc';
	$fp = fopen($file, 'w');

	// header
	$data  = "#########################################\n";
	$data .= "# This file is automatically generated by\n";
	$data .= "# the player System configuration page. \n";
	$data .= "#########################################\n\n";

	$data .= "# Last.FM username and password (MD5 hashed)\n";
	$data .= "username: " . $_SESSION['mpdasuser'] . "\n";
	$data .= "password: " . (empty($_SESSION['mpdaspwd']) ? '' : md5($_SESSION['mpdaspwd'])) . "\n\n";
	
	$data .= "# Optional MPD host, password and port\n";
	$data .= "#host:\n";
	$data .= "#mpdpassword:\n";
	$data .= "#port:\n\n";

	$data .= "# Change the user mpdas runs as\n";
	$data .= "runas: mpd\n\n";

	$data .= "# Print debug information (1/0)\n";
	$data .= "#debug:\n\n";

	$data .= "# Will scrobble to Libre.fm if set to librefm\n";
	$data .= "#service:\n";

	fwrite($fp, $data);
	fclose($fp);
}

// auto-configure settings at worker startup
function autoConfig($cfgfile) {
	$contents = file_get_contents($cfgfile);

	$autocfg = array();
	$line = strtok($contents, "\n");

	while ($line) {
		$firstchr = substr($line, 0, 1);

		if (!($firstchr == '#' || $firstchr == '[')) {
			list ($element, $value) = explode("=", $line, 2);
			$autocfg[$element] = $value;
		}

		$line = strtok("\n");
	}

	// [names]

	// host name
	sysCmd('/var/www/command/util.sh chg-name host "moode" ' . '"' . $autocfg['hostname'] . '"');
	playerSession('write', 'hostname', $autocfg['hostname']);
	workerLog('worker: hostname (' . $autocfg['hostname'] . ')');

	// browser title
	sysCmd('/var/www/command/util.sh chg-name browsertitle "MoOde Player" ' . '"' . $autocfg['browsertitle'] . '"');
	playerSession('write', 'browsertitle', $autocfg['browsertitle']);
	workerLog('worker: browsertitle (' . $autocfg['browsertitle'] . ')');

	// airplay name
	playerSession('write', 'airplayname', $autocfg['airplayname']);
	workerLog('worker: airplayname (' . $autocfg['airplayname'] . ')');

	// upnp name
	sysCmd('/var/www/command/util.sh chg-name upnp "Moode UPNP" ' . '"' . $autocfg['upnpname'] . '"');
	playerSession('write', 'upnpname', $autocfg['upnpname']);
	workerLog('worker: upnpname (' . $autocfg['upnpname'] . ')');

	// dlna name
	sysCmd('/var/www/command/util.sh chg-name dlna "Moode DLNA" ' . '"' . $autocfg['dlnaname'] . '"');
	playerSession('write', 'dlnaname', $autocfg['dlnaname']);
	workerLog('worker: dlnaname (' . $autocfg['dlnaname'] . ')');

	// mpd zeroconf name
	sysCmd('/var/www/command/util.sh chg-name mpdzeroconf ' . "'" . '"moode"' . "'" . ' ' . "'" . '"' . $autocfg['mpdzeroconf'] . '"' . "'");
	cfgdb_update('cfg_mpd', cfgdb_connect(), 'zeroconf_name', $autocfg['mpdzeroconf']);
	workerLog('worker: mpdzeroconf (' . $autocfg['mpdzeroconf'] . ')');

	// [network]

	$dbh = cfgdb_connect();
	$netcfg = sdbquery('select * from cfg_network', $dbh);

	// wlan ssid, security, and password
	$value = array('method' => $netcfg[1]['method'], 'ipaddr' => $netcfg[1]['ipaddr'], 'netmask' => $netcfg[1]['netmask'], 'gateway' => $netcfg[1]['gateway'], 'pridns' => $netcfg[1]['pridns'], 'secdns' => $netcfg[1]['secdns'], 'wlanssid' => $autocfg['wlanssid'], 'wlansec' => $autocfg['wlansec'], 'wlanpwd' => $autocfg['wlanpwd']);
	cfgdb_update('cfg_network', $dbh, 'wlan0', $value);
	cfgNetIfaces();
	workerLog('worker: wlanssid (' . $autocfg['wlanssid'] . ')');
	workerLog('worker: wlansec (' . $autocfg['wlansec'] . ')');
	workerLog('worker: wlanpwd (' . $autocfg['wlanpwd'] . ')');

	// apd ssid, channel and passwpord
	playerSession('write', 'apdssid', $autocfg['apdssid']);
	playerSession('write', 'apdchan', $autocfg['apdchan']);
	playerSession('write', 'apdpwd', $autocfg['apdpwd']);
	cfgHostApd();
	workerLog('worker: apdssid (' . $autocfg['apdssid'] . ')');
	workerLog('worker: apdchan (' . $autocfg['apdchan'] . ')');
	workerLog('worker: apdpwd (' . $autocfg['apdpwd'] . ')');

	// [services]

	// airplay receiver
	playerSession('write', 'airplaysvc', $autocfg['airplaysvc']);
	workerLog('worker: airplayrcvr (' . $autocfg['airplaysvc'] . ')');

	// upnp renderer
	playerSession('write', 'upnpsvc', $autocfg['upnpsvc']);
	workerLog('worker: upnprenderer (' . $autocfg['upnpsvc'] . ')');

	// dlna server
	playerSession('write', 'dlnasvc', $autocfg['dlnasvc']);
	workerLog('worker: dlnaserver (' . $autocfg['dlnasvc'] . ')');

	// [other]

	// timezone
	sysCmd('/var/www/command/util.sh set-timezone ' . $autocfg['timezone']);
	playerSession('write', 'timezone', $autocfg['timezone']);
	workerLog('worker: timezone (' . $autocfg['timezone'] . ')');

	// theme color
	switch (strtolower($autocfg['themecolor'])) {
		case 'alizarin':
			sysCmd('/var/www/command/util.sh alizarin'); // don't specify colors
			break;
		case 'amethyst':
			sysCmd('/var/www/command/util.sh amethyst 9b59b6 8e44ad'); // #hexlight #hexdark
			break;
		case 'bluejeans':
			sysCmd('/var/www/command/util.sh bluejeans 335db6 1a439c');
			break;
		case 'carrot':
			sysCmd('/var/www/command/util.sh carrot e67e22 d35400');
			break;
		case 'emerald':
			sysCmd('/var/www/command/util.sh emerald 2ecc71 27ae60');
			break;
		case 'fallenleaf':
			sysCmd('/var/www/command/util.sh fallenleaf e5a646 cb8c3e');
			break;
		case 'grass':
			sysCmd('/var/www/command/util.sh grass 90be5d 7ead49');
			break;
		case 'herb':
			sysCmd('/var/www/command/util.sh herb 48929b 317589');
			break;
		case 'lavender':
			sysCmd('/var/www/command/util.sh lavender 9a83d4 876dc6');
			break;
		case 'river':
			sysCmd('/var/www/command/util.sh river 3498db 2980b9');
			break;
		case 'rose':
			sysCmd('/var/www/command/util.sh rose d479ac c1649b');
			break;
		case 'turquoise':
			sysCmd('/var/www/command/util.sh turquoise 1abc9c 16a085');
			break;
	}

	playerSession('write', 'themecolor', $autocfg['themecolor']);
	workerLog('worker: themecolor (' . $autocfg['themecolor'] . ')');

	// remove config file
	sysCmd('rm ' . $cfgfile);
	workerLog('worker: cfgfile removed');
}

// check for available software update
function checkForUpd($path) {
	// $path
	// - http: //moodeaudio.org/downloads/
	// - /var/www/

	// check for update package ex: update-r26.txt
	if (false === ($tmp = file_get_contents($path . 'update-' . getPkgId() . '.txt'))) {
		$result['pkgdate'] = 'None'; 
	}
	else {
		$result = parseDelimFile($tmp, '=');
	}

	return $result;
}

// get moode release version and date
function getMoodeRel($options) {
	if ($options === 'verbose') {
		// major.minor yyyy-mm-dd ex: 2.6 2016-06-07
		$result = sysCmd("awk '/Release: /{print $2 " . '" "' . " $3;}' /var/www/footer.php | sed 's/,//'");
		return $result[0];
	}
	else {
		// rXY ex: r26
		$result = sysCmd("awk '/Release: /{print $2;}' /var/www/footer.php | sed 's/,//'");
		$str = 'r' . str_replace('.', '', $result[0]);
		return $str;
	}
}

function getPkgId () {
	$result = sdbquery("select value from cfg_engine where param='pkgid'", cfgdb_connect());
	return getMoodeRel() . $result[0]['value'];
}

// parse result of mpd outputs cmd
function parseMpdOutputs($resp) {
	$array = array();
	$line = strtok($resp, "\n");

	while ($line) {
		list ($element, $value) = explode(": ", $line, 2);

		if ($element == 'outputid') {
			$id = $value;				
			$array[$id] = 'MPD output ' . ($id + 1) . ' '; 
		}

		if ($element == 'outputname') {
			$array[$id] .= $value;

		}

		if ($element == 'outputenabled') {
			$array[$id] .= $value == '0' ? ' (disabled)' : ' (enabled)';
		}

		$line = strtok("\n");
	}

	return $array;
}

// enable/disable pi3 wifi-bt adapter
function disableWifiBt() {
	$file = '/etc/modprobe.d/wifi-bt.conf';
	$fp = fopen($file, 'w');

	// header
	$data  = "#########################################\n";
	$data .= "# This file is automatically generated by\n";
	$data .= "# the player System configuration page. \n";
	$data .= "#########################################\n\n";

	$data .= "# WiFi\n";
	$data .= "blacklist brcmfmac\n";
	$data .= "blacklist brcmutil\n";
	$data .= "# BT\n";
	$data .= "blacklist btbcm\n";
	$data .= "blacklist hci_uart\n";

	fwrite($fp, $data);
	fclose($fp);

	sysCmd('systemctl disable bluetooth');
}

// config squeezelite
function cfgSqueezelite() {
	// load settings
	$result = cfgdb_read('cfg_sl', cfgdb_connect());
	
	// generate config file output
	foreach ($result as $row) {
		if ($row['param'] == 'AUDIODEVICE') {
			$output .= $row['param'] . '="hw:' . $row['value'] . ',0"' . "\n";
		}
		else {
			$output .= $row['param'] . '=' . $row['value'] . "\n";
		}
	}
	
	// write config file
	$fh = fopen('/etc/squeezelite.conf', 'w');
	fwrite($fh, $output);
	fclose($fh);
}

// restart squeezelite
function slRestart () {
	sysCmd('killall -s 9 squeezelite-' . $_SESSION['procarch']);
	$result = sysCmd('pgrep -l squeezelite-' . $_SESSION['procarch']);
	$count = 10;
	while ($result[0] && $count > 0) {				
		sleep(1);
		$result = sysCmd('pgrep -l squeezelite-' . $_SESSION['procarch']);
		--$count;
	}			
	sysCmd('systemctl start squeezelite-' . $_SESSION['procarch']);
}

function cfgI2sOverlay($i2sDevice) {
	// get device config
	$result = cfgdb_read('cfg_audiodev', cfgdb_connect(), $i2sDevice);

	 // remove existing overlay(s)			
	sysCmd('sed -i /dtoverlay/d /boot/config.txt');
		
	// reset to 'none' for certain devices that are only supported in Advanced kernel
	if ($result[0]['kernel'] == 'Advanced' && $_SESSION['kernel'] == 'Standard') {
		$i2sDevice = 'none';
	}

	// configure new overlay or deactivate
	if ($i2sDevice == 'none') {
		sysCmd('sed -i "s/dtparam=audio=off/dtparam=audio=on/" /boot/config.txt');
	}
	else {
		sysCmd('sed -i "s/dtparam=audio=on/dtparam=audio=off/" /boot/config.txt');
		//$result = cfgdb_read('cfg_audiodev', cfgdb_connect(), $i2sDevice);
		if ($_SESSION['kernel'] == 'Advanced') {
			sysCmd('echo dtoverlay=' . $result[0]['advdriver'] . ' >> /boot/config.txt');
			// certain devices get an extra overlay
			if ($i2sDevice == 'Buffalo II/IIIse' || $i2sDevice == 'DDDAC1794 NOS') {
				sysCmd('echo dtoverlay=' . $result[0]['settings'] . ' >> /boot/config.txt');
			}
		}
		else {
			sysCmd('echo dtoverlay=' . $result[0]['driver'] . ' >> /boot/config.txt');
		}
	}

	// store for Customize and Audio info popups
	playerSession('write', 'adevname', $i2sDevice);
}

// create enhanced metadata
function enhanceMetadata($current, $sock, $flags) {
	define(LOGO_ROOT_DIR, 'images/radio-logos/');
	define(DEF_RADIO_COVER, 'images/radio-cover.jpg');
	define(DEF_COVER, 'images/default-cover.jpg');

	$song = parseCurrentSong($sock);
	$current['file'] = $song['file'];
	
	// NOTE any of these might be '' null string
	$current['track'] = $song['Track'];
	$current['date'] = $song['Date'];
	$current['composer'] = $song['Composer'];
	
	if ($current['file'] == null) {
		$current['artist'] = '';
		$current['title'] = '';
		$current['album'] = '';
		$current['coverurl'] = DEF_COVER;
	} else {
		// get encoded bit depth and sample rate
		$current['encoded'] = $flags == 'mediainfo' ? getEncodedAt($song, 'default') : '';
	
		// itunes aac or aiff file
		$ext = getFileExt($song['file']);
		if (isset($song['Name']) && ($ext == 'm4a' || $ext == 'aif' || $ext == 'aiff')) {
			$current['artist'] = isset($song['Artist']) ? $song['Artist'] : 'Unknown artist';
			$current['title'] = $song['Name']; 
			$current['album'] = isset($song['Album']) ? $song['Album'] : 'Unknown album';
			$current['coverurl'] = '/coverart.php/' . rawurlencode($song['file']); 
			
		// radio station
		} else if (isset($song['Name']) || (substr($song['file'], 0, 4) == 'http' && !isset($song['Artist']))) {
			$current['artist'] = 'Radio station';
			
			if (!isset($song['Title']) || trim($song['Title']) == '') {
				$current['title'] = $song['file'];
			} else {
				// use custom name for particular station
				$current['title'] = $_SESSION[$song['file']]['name'] == 'Classic And Jazz' ? 'CLASSIC & JAZZ (Paris - France)' : $song['Title'];
			}
			
			if (isset($_SESSION[$song['file']])) {
				// use xmitted name for Soma stations
				$current['album'] = substr($_SESSION[$song['file']]['name'], 0, 4) == 'Soma' ? $song['Name'] : $_SESSION[$song['file']]['name'];
				if ($_SESSION[$song['file']]['logo'] == 'local') {
					$current['coverurl'] = LOGO_ROOT_DIR . $_SESSION[$song['file']]['name'] . ".png"; // local logo image
				} else {
					$current['coverurl'] = $_SESSION[$song['file']]['logo']; // Soma logo url
				}
			} else {
				// not in radio station table, use xmitted name or 'unknown'
				$current['album'] = isset($song['Name']) ? $song['Name'] : 'Unknown station';
				$current['coverurl'] = DEF_RADIO_COVER;
			}
			
		// song file or upnp url	
		} else {
			$current['artist'] = isset($song['Artist']) ? $song['Artist'] : 'Unknown artist';
			$current['title'] = isset($song['Title']) ? $song['Title'] : pathinfo(basename($song['file']), PATHINFO_FILENAME);
			$current['album'] = isset($song['Album']) ? $song['Album'] : 'Unknown album';
			$current['coverurl'] = substr($song['file'], 0, 4) == 'http' ? getUpnpCoverUrl() : '/coverart.php/' . rawurlencode($song['file']); 
		}
	}
	
	return $current;
}