#!/usr/bin/php5
<?php
/**
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
 * along with Moode Audio Player; see the file COPYING.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * 2016-06-07 2.6 TC volume cli
 * 2016-08-28 2.7 TC add quotes around amixname, update range and regex logic
 * 2016-11-27 3.0 TC optimize sql statements
 *
 */
 
require_once dirname(__FILE__) . '/inc/playerlib.php';

// initialize
if (false === ($sock = openMpdSock('localhost', 6600))) {
	workerLog('vol: Connection to mpd failed');
	exit("error: openMpdSock() failed\n");	
}

if (false === ($dbh = cfgdb_connect())) {
	workerLog('vol: Connection to sqlite failed');
	exit("error: cfgdb_connect() failed\n");	
}

if (!isset($argv[1])) {
	$result = sdbquery("select value from cfg_engine where id='35'", $dbh);
	exit($result[0]['value'] . "\n");
}
	
if ($argv[1] == '-help') {
	exit("vol.php with no arguments will print the current volume level\n" .
	"vol.php restore will set alsa/mpd volume based on current knob setting\n" .
	"vol.php <level between 0-100>, mute (toggle), up <step> or dn <step>, -help\n");
}

// process volume cmds

$result = sdbquery("select param, value from cfg_engine where id in ('32', '34', '35', '36', '37', '39', '40')", $dbh);
$array = array();
foreach ($result as $row) {
	$array[$row['param']] = $row['value'];
}

$device = empty(file_get_contents('/proc/asound/card1/id')) ? '0' : '1'; 

// mute toggle
if ($argv[1] == 'mute') {
	if ($array['volmute'] == '1') {
		$result = sdbquery("update cfg_engine set value='0' where id='36'", $dbh);
		$volmute = '0';
		$level = $array['volknob']; 
	}
	else {
		$result = sdbquery("update cfg_engine set value='1' where id='36'", $dbh);
		$volmute = '1';
	}
}
else {
	// restore alsa/mpd volume
	if ($argv[1] == 'restore') {
		$level = $array['volknob']; 
	}
	// volume step
	elseif ($argv[1] == 'up') {
		$level = $array['volknob'] + $argv[2]; 
	}
	elseif ($argv[1] == 'dn') {
		$level = $array['volknob'] - $argv[2]; 
	}
	// volume level
	else {
		$level = $argv[1]; 
	}

	// numeric check
	if (!preg_match('/^[+-]?[0-9]+$/', $level)) {
		workerLog('vol: fail numeric check)');
		exit("Level must only contain digits 0-9\n");
	}

	// range check
	if ($level < 0) {
		$level = 0;
	}
	elseif ($level > $array['volwarning']) {
		workerLog('vol: fail limit check)');
		exit('Volume exceeds warning limit ' . $array['volwarning'] . "\n");
	}
	else {
		// update knob
		$result = sdbquery("update cfg_engine set value='" . $level . "' where id='35'", $dbh);
		//workerLog('vol: result=(' . $result . ')');
	}
}

// mute if indicated
if ($volmute == '1') {
	sendMpdCmd($sock, 'setvol 0');
	$resp = readMpdResp();
	exit();
}

// set volume level
if ($array['mpdmixer'] == 'hardware') {
	// hardware volume: update ALSA volume --> MPD volume --> MPD idle timeout --> UI updated
	if ($array['volcurve'] == 'Yes') {
		sysCmd('amixer -c ' . $device . ' sset ' . '"' . $array['amixname']  . '"' . ' -M ' . $level . '%');
	}
	else {
		sysCmd('amixer -c ' . $device . ' sset ' . '"' . $array['amixname'] . '"' . ' ' . $level . '%');
	}
}
else {
	// software volume: update MPD volume --> MPD idle timeout --> UI updated
	sendMpdCmd($sock, 'setvol ' . $level);
	$resp = readMpdResp();
}

closeMpdSock($sock);
