#!/usr/bin/php5
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
 * - add watchdog to set of services started
 * - remove code that reestablished symlinks in /var/lib/mpd/music (causing circular symlinks)
 * - add job for software updater
 * - add moode release version and date to startup log
 * - del sysCmd('chmod 777 /run'), session_save_path('/run'), settings handled in conf files
 * - add code to set keyboard and varient: Richard Parslow
 * - add $_SESSION['pkgdate']
 * - pass $current instead of $current['file'] in getEncodedAt()
 * - add capability to append suffix '-test' to mooderel
 * - add crossfeed job
 * - list mpd outputs in worker log
 * - fix null printed in Moode log for USB sources
 * - remove "chmod 777 /run/sess*", not needed anymore since memcache
 * - add quotes around amixname
 * - add wifibt status
 * - bump wait time for eth0 check
 * - add mpd state to currentsong.txt
 * - replace IQ_rot with new rotenc universal rotary encoder driver
 * 2016-11-27 3.0 TC
 * - improve chip option handling
 * - add install-kernel job
 * - handle adv i2s audio overlays
 * - add squeezelite job
 * - add cpu governor job
 * - add compactdb job
 * - bump to moodeOS 1.1
 * 2016-12-05 3.1 TC squeezelite-armv6l and -armv7l
 *
 */

require_once dirname(__FILE__) . '/../inc/playerlib.php';

// begin startup
sysCmd('truncate /var/log/moode.log --size 0');
workerLog('worker: Startup');

// daemonize
$lock = fopen('/run/worker.pid', 'c+');
if (!flock($lock, LOCK_EX | LOCK_NB)) {
	workerLog('worker: Already running');
	die('already running');
}

switch ($pid = pcntl_fork()) {
	case -1:
		$logmsg = 'worker: Unable to fork';
		workerLog($logmsg);
		die($logmsg . "\n");
	case 0: // child process
		break;
	default: // parent process
		fseek($lock, 0);
		ftruncate($lock, 0);
		fwrite($lock, $pid);
		fflush($lock);
		exit;
}
 
if (posix_setsid() === -1) {
	$logmsg = 'worker: Could not setsid';
	workerLog($logmsg);
	die($logmsg . "\n");
}
 
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);

$stdIn = fopen('/dev/null', 'r'); // set fd/0
$stdOut = fopen('/dev/null', 'w'); // set fd/1
$stdErr = fopen('php://stdout', 'w'); // a hack to duplicate fd/1 to 2

pcntl_signal(SIGTSTP, SIG_IGN);
pcntl_signal(SIGTTOU, SIG_IGN);
pcntl_signal(SIGTTIN, SIG_IGN);
pcntl_signal(SIGHUP, SIG_IGN);

// permissions
sysCmd('chmod -R 0777 /var/www/db');

// cache cfg_engine table in session vars
playerSession('open', '', ''); // load session vars

// store platform data
playerSession('write', 'hdwrrev', getHdwrRev());
playerSession('write', 'moodeosver', '1.1');
playerSession('write', 'kernelver', strtok(shell_exec('uname -r'),"\n"));
playerSession('write', 'procarch', strtok(shell_exec('uname -m'),"\n"));
$mpdver = explode(" ", strtok(shell_exec('mpd -V | grep "Music Player Daemon"'),"\n"));
playerSession('write', 'mpdver', $mpdver[3]);
$lastinstall = checkForUpd('/var/www/');
$_SESSION['pkgdate'] = $lastinstall['pkgdate'];

// auto-configure if indicated
if (file_exists('/boot/moodecfg.txt')) {
	workerLog('worker: Auto-configure initiated');
	autoConfig('/boot/moodecfg.txt');
	sysCmd('reboot');
	//workerLog('worker: Auto-configure done, reboot to make changes effective');
}

// log 
workerLog('worker: Host (' . $_SESSION['hostname'] . ')');
workerLog('worker: Hdwr (' . $_SESSION['hdwrrev'] . ')');
workerLog('worker: Arch (' . $_SESSION['procarch'] . ')');
workerLog('worker: Kver (' . $_SESSION['kernelver'] . ')');
workerLog('worker: Ktyp (' . $_SESSION['kernel'] . ')');
workerLog('worker: Gov  (' . $_SESSION['cpugov'] . ')');
workerLog('worker: OS   (moodeOS ' . $_SESSION['moodeosver'] . ')');
workerLog('worker: Rel  (Moode ' . getMoodeRel('verbose') . ')'); // X.Y yyyy-mm-dd ex: 2.6 2016-06-07
workerLog('worker: Upd  (' . $_SESSION['pkgdate'] . ')');
workerLog('worker: MPD  (' . $_SESSION['mpdver'] . ')');

// cache radio station table in session vars
$dbh = cfgdb_connect();
$result = cfgdb_read('cfg_radio', $dbh);
foreach ($result as $row) {
	$_SESSION[$row['station']] = array('name' => $row['name'], 'permalink' => $row['permalink'], 'logo' => $row['logo']);
}
workerLog('worker: Session loaded');
workerLog('worker: Debug logging (' . ($_SESSION['debuglog'] == '1' ? 'on' : 'off') . ')');

// ensure certain files exist
workerLog('worker: File check...');
if (!file_exists('/var/www/currentsong.txt')) {sysCmd('touch /var/www/currentsong.txt');}
if (!file_exists('/var/www/libcache.json')) {sysCmd('touch /var/www/libcache.json');}
if (!file_exists('/var/www/playhistory.log')) {sysCmd('touch /var/www/playhistory.log');}
if (!file_exists('/var/log/moode.log')) {sysCmd('touch /var/log/moode.log');}
// sps metadata
if (!file_exists('/var/www/images/spscover.jpg')) {sysCmd('touch /var/www/images/spscover.jpg');}
if (!file_exists('/var/www/images/spscover.png')) {sysCmd('touch /var/www/images/spscover.png');}
if (!file_exists('/var/www/spscache.json')) {sysCmd('touch /var/www/spscache.json');}

// permissions
sysCmd('chmod 777 /var/lib/mpd/music/RADIO/*.*');
sysCmd('chmod 777 /var/www/currentsong.txt');
sysCmd('chmod 777 /var/www/libcache.json');
sysCmd('chmod 777 /var/www/playhistory.log');
sysCmd('chmod 666 /var/log/moode.log');
// sps metadata
sysCmd('chmod 777 /var/www/images/spscover.jpg');
sysCmd('chmod 777 /var/www/images/spscover.png');
sysCmd('chmod 777 /var/www/spscache.json');
workerLog('worker: File check ok');

// set auto-shuffle active = 0 so no conflict w/auto play at start
playerSession('write', 'ashuffle', '0');
sysCmd('killall ashuffle > /dev/null 2>&1 &'); // necessary ?
workerLog('worker: Auto-shuffle deactivated');

// log status of usb sources
$result = sysCmd('ls /media');
$logmsg = $result[0] == '' ? 'none attached' : $result[0];
workerLog('worker: USB sources ' . '(' . $logmsg . ')');

// mpd scheduler policy
workerLog('worker: MPD scheduler policy ' . '(' . ($_SESSION['mpdsched'] == 'other' ? 'time-share' : $_SESSION['mpdsched']) . ')');

// start mpd
sysCmd("systemctl start mpd");
workerLog('worker: MPD started');
	
// remove circular symlinks set by ?
sysCmd('rm /media/media');
#sysCmd('rm /var/lib/mpd/music/music');

// start ap mode if indicated
$result = sdbquery('select * from cfg_network', $dbh);
$wlan0 = sysCmd('ip addr list |grep wlan0');

if (!empty($wlan0[0])) { // adapter exists
	workerLog('worker: wlan0 exists');
	if (empty($result[1]['wlanssid'])) { // wlan0 ssid blank
		workerLog('worker: wlan0 AP mode started');
		$_SESSION['apactivated'] = true;
	
		sysCmd('sed -i "s/#interface wlan0/interface wlan0/" /etc/dhcpcd.conf');
		sysCmd('sed -i "s/#static ip_address/static ip_address/" /etc/dhcpcd.conf');
		sysCmd('systemctl restart dhcpcd');
		sysCmd('systemctl daemon-reload');
	
		sysCmd('sudo systemctl start dnsmasq');
		sysCmd('sudo systemctl start hostapd');
	} else { // ssid has a value so let Linux try it
		workerLog('worker: wlan0 trying SSID (' . $result[1]['wlanssid'] . ')');
		$_SESSION['apactivated'] = false;
	}
} else {
	workerLog('worker: wlan0 does not exist' . ($_SESSION['wifibt'] == '0' ? ' (off)' : ''));
	$_SESSION['apactivated'] = false;
}

// start minidlna server if indicated
if (isset($_SESSION['dlnasvc']) && $_SESSION['dlnasvc'] == 1) {
	startMiniDlna();
	workerLog('worker: DLNA server started');
}

// start upnp renderer if indicated
if (isset($_SESSION['upnpsvc']) && $_SESSION['upnpsvc'] == 1) {
	sysCmd('/etc/init.d/upmpdcli start > /dev/null 2>&1 &');
	workerLog('worker: UPnP renderer started');
} 

// start squeezelite renderer if indicated
if (isset($_SESSION['slsvc']) && $_SESSION['slsvc'] == 1) {
	sysCmd('systemctl start squeezelite-' . $_SESSION['procarch']);
	workerLog('worker: Squeezelite renderer started');
} 

// start rotary encoder driver if indicated
if (isset($_SESSION['rotaryenc']) && $_SESSION['rotaryenc'] == 1) {
	sysCmd('/usr/local/bin/rotenc > /dev/null 2>&1 &');
	workerLog('worker: Rotary encoder driver loaded');
} 

// start lcd updater engine if indicated
if (isset($_SESSION['lcdup']) && $_SESSION['lcdup'] == 1) {
	startLcdUpdater();
	workerLog('worker: LCD updater engine started');
} 

// start audio scrobbler if indicated
if (isset($_SESSION['mpdassvc']) && $_SESSION['mpdassvc'] == 1) {
	sysCmd('/usr/local/bin/mpdas > /dev/null 2>&1 &');
	workerLog('worker: Audio scrobbler started');
} 

// turn on/off hdmi port
$cmd = $_SESSION['hdmiport'] == '1' ? 'tvservice -p' : 'tvservice -o';
sysCmd($cmd . ' > /dev/null');
workerLog('worker: HDMI port ' . ($_SESSION['hdmiport'] == '1' ? 'on' : 'off'));

// log audio device info
$logmsg = 'worker: Audio ';
if ($_SESSION['i2sdevice'] == 'none') {
	$result = sdbquery("select value_player from cfg_mpd where param='device'", $dbh);
	$logmsg .= $result[0]['value_player'] == '1' ? '(USB audio device)' : '(On-board audio device)';
	workerLog($logmsg);
} else {
	workerLog($logmsg . '(I2S audio device)');
	workerLog($logmsg . '(' . $_SESSION['i2sdevice'] . ')');
}

// configure chip options for certain devices
$result = cfgdb_read('cfg_audiodev', $dbh, $_SESSION['i2sdevice']);
$chips = array('Burr Brown PCM5242','Burr Brown PCM5142','Burr Brown PCM5122','Burr Brown PCM5121','Burr Brown PCM5122 (PCM5121)','Burr Brown TAS5756');
if (in_array($result[0]['dacchip'], $chips) && $result[0]['settings'] != '') {
	cfgPcm512x($result[0]['settings']);
	workerLog('worker: PCM52xx/PCM51xx/TAS5756 chip options applied');
}

// ensure audio output is unmuted
if ($_SESSION['i2sdevice'] == 'IQaudIO Pi-AMP+') {	
	sysCmd('/var/www/command/util.sh unmute-pi-ampplus');
	workerLog('worker: IQaudIO Pi-AMP+ unmuted');
} else if ($_SESSION['i2sdevice'] == 'IQaudIO Pi-DigiAMP+') {	
	sysCmd('/var/www/command/util.sh unmute-pi-digiampplus');
	workerLog('worker: IQaudIO Pi-DigiAMP+ unmuted');
} else {
	sysCmd('/var/www/command/util.sh unmute-default');
	workerLog('worker: ALSA outputs unmuted');
}

// store alsa mixer name for use by util.sh get/set-alsavol and vol.sh & .php
playerSession('write', 'amixname', getMixerName($_SESSION['i2sdevice']));
workerLog('worker: ALSA mixer name (' . $_SESSION['amixname'] . ')');
workerLog('worker: MPD volume control (' . $_SESSION['mpdmixer'] . ')');

// list mpd outputs
$sock = openMpdSock('localhost', 6600);
sendMpdCmd($sock, 'outputs');
$array = parseMpdOutputs(readMpdResp($sock));
workerLog('worker: ' . $array[0]);
workerLog('worker: ' . $array[1]);

// check for presence of hardware volume controller
$result = sysCmd('/var/www/command/util.sh get-alsavol ' . '"' . $_SESSION['amixname'] . '"');
if (substr($result[0], 0, 6 ) == 'amixer') {
	playerSession('write', 'alsavolume', 'none'); // hardware volume controller not detected
	workerLog('worker: Hdwr volume controller not detected');
} else {
	$result[0] = str_replace('%', '', $result[0]);
	playerSession('write', 'alsavolume', $result[0]); // volume level
	workerLog('worker: Hdwr volume controller exists');
}

// restore volume level
sysCmd('/var/www/vol.sh ' . $_SESSION['volknob']);
workerLog('worker: Volume level (' . $_SESSION['volknob'] . ') restored');

// check to see if ip addresses have been assiened

// wait up to N secs for wlan0 address to be assigned
$ipaddr = '';
if (!empty($wlan0)) { // $wlan0 is from AP mode code earlier in worker
	for ($i = 1; $i <= 3; $i++) {
		$ipaddr = sysCmd("ip addr list wlan0 |grep \"inet \" |cut -d' ' -f6|cut -d/ -f1");
	
		if (!empty($ipaddr[0])) {
			break;
		} else {
			workerLog('worker: wlan0 wait '. $i . ' for address');
			usleep(3000000); // default 3 secs
			//usleep(500000); // .5 secs
		}
	}
}
$logmsg = !empty($ipaddr[0]) ? 'wlan0 (' . $ipaddr[0] . ')' : ($_SESSION['apactivated'] == true ? 'wlan0 unable to start AP mode' : 'wlan0 address not assigned');
workerLog('worker: ' . $logmsg);

// wait up to N secs for eth0 address to be assigned
$ipaddr = '';
$eth0 = sysCmd('ip addr list |grep eth0');
if (!empty($eth0)) {
	workerLog('worker: eth0 exists');

	// bypass eth0 check?
	if ($_SESSION['eth0chk'] == '1') {
		for ($i = 1; $i <= 3; $i++) {
			$ipaddr = sysCmd("ip addr list eth0 |grep \"inet \" |cut -d' ' -f6|cut -d/ -f1");
		
			if (!empty($ipaddr[0])) {
				break;
			} else {
				workerLog('worker: eth0 wait '. $i . ' for address');
				usleep(3000000); // default 3 secs
				//usleep(500000); // .5 secs
			}
		}
	}
}
else {
	workerLog('worker: eth0 does not exist');
}
$logmsg = !empty($ipaddr[0]) ? 'eth0 (' . $ipaddr[0] . ')' : 'eth0 address not assigned';
workerLog('worker: ' . $logmsg);

// start shairport-sync if indicated
if (isset($_SESSION['airplaysvc']) && $_SESSION['airplaysvc'] == 1) {
	startSps();
	workerLog('worker: Airplay receiver started');
	workerLog('worker: Airplay volume (' . $_SESSION['airplayvol'] . ')');
}

// mount nas sources
$result = wrk_sourcemount('mountall');
workerLog('worker: NAS sources ' . '(' . $result . ')');

// reset consume mode to off
sendMpdCmd($sock, 'consume 0');
$resp = readMpdResp($sock);
workerLog('worker: MPD consume reset to off');

// mpd crossfade setting
workerLog('worker: MPD crossfade (' . ($_SESSION['mpdcrossfade'] == '0' ? 'off' : $_SESSION['mpdcrossfade'] . ' secs')  . ')');

// auto-play last played item if indicated
if ($_SESSION['autoplay'] == '1') {
	$status = parseStatus(getMpdStatus($sock));
	sendMpdCmd($sock, 'playid ' . $status['songid']);
	$resp = readMpdResp($sock);
	workerLog('worker: Autoplay on');
} else {
	sendMpdCmd($sock, 'stop');
	$resp = readMpdResp($sock);
}
closeMpdSock($sock);

// start watchdog monitor
sysCmd('/var/www/command/watchdog.sh > /dev/null 2>&1 &');
workerLog('worker: Watchdog started');

// clock radio globals
$ckstart = $_SESSION['ckradstart'];
$ckstop = $_SESSION['ckradstop'];

// inizialize worker job quque
$_SESSION['w_queue'] = '';
$_SESSION['w_queueargs'] = '';
$_SESSION['w_lock'] = 0;
$_SESSION['w_active'] = 0;

// close session for startup
session_write_close();

// end startup
workerLog('worker: End startup');
workerLog('worker: Ready');

// begin worker loop
while (1) {
	sleep(3);
		
	session_start();

	if ($_SESSION['extmeta'] == '1') {
		updExtMetaFile();
	}

 	if ($_SESSION['ckrad'] == 'Yes') {
		chkClockRadio();		
	}

	if ($_SESSION['playhist'] == 'Yes') {
		updPlayHistory();		
	}

	if ($_SESSION['w_active'] == 1 && $_SESSION['w_lock'] == 0) {
		runQueuedJob();
	}

	session_write_close();	
}

// worker functions

function updExtMetaFile() {
	// current metadata
	$sock = openMpdSock('localhost', 6600);
	$current = parseStatus(getMpdStatus($sock));
	$current = enhanceMetadata($current, $sock, 'nomediainfo');
	closeMpdSock($sock);

	// file  metadata
	$filemeta = parseDelimFile(file_get_contents('/var/www/currentsong.txt'), '=');

	// write metadata to file for external applications
	if ($current['title'] != $filemeta['title'] || $current['album'] != $filemeta['album'] || $_SESSION['volknob'] != $filemeta['volume'] || 
		$_SESSION['volmute'] != $filemeta['mute'] || $current['state'] != $filemeta['state']) {	

		$fh = fopen('/var/www/currentsong.txt', 'w') or die('file open failed on /var/www/currentsong.txt');
		// default 
		$data = 'file=' . $current['file'] . "\n"; 
		$data .= 'artist=' . $current['artist'] . "\n";
		$data .= 'album=' . $current['album'] . "\n";
		$data .= 'title=' . $current['title'] . "\n";
		$data .= 'coverurl=' . $current['coverurl'] . "\n";
		// xtra tags
		$data .= 'track=' . $current['track'] . "\n";
		$data .= 'date=' . $current['date'] . "\n";
		$data .= 'composer=' . $current['composer'] . "\n";
		// other
		$data .= 'encoded=' . getEncodedAt($current, 'default') . "\n";
		$data .= 'volume=' . $_SESSION['volknob'] . "\n";
		$data .= 'mute=' . $_SESSION['volmute'] . "\n";
		$data .= 'state=' . $current['state'] . "\n";
		
		fwrite($fh, $data);
		fclose($fh);
	}
}

function chkClockRadio() {
	$curtime = date("hi A");
	$ckretry = 2;
	
	if ($curtime == $GLOBALS['ckstart']) {
		$GLOBALS['ckstart'] = ''; // reset so only done once
		$sock = openMpdSock('localhost', 6600);

		// find playlist item
		sendMpdCmd($sock, 'playlistfind file ' . '"' . $_SESSION['ckraditem'] . '"');
		$resp = readMpdResp($sock);
		$array = array();
		$line = strtok($resp, "\n");
		while ($line) {
			list($element, $value) = explode(': ', $line, 2);
			$array[$element] = $value;
			$line = strtok("\n");
		} 

		// send play cmd
		sendMpdCmd($sock, 'play ' . $array['Pos']);
		$resp = readMpdResp($sock);
		closeMpdSock($sock);
		
		// set volume
		sysCmd('/var/www/vol.sh ' . $_SESSION['ckradvol']);
		
	} else if ($curtime == $GLOBALS['ckstop']) {
		$GLOBALS['ckstop'] = '';  // reset so this is only done once
		$sock = openMpdSock('localhost', 6600);

		// send several stop commands for robustness
		while ($ckretry > 0) {
			sendMpdCmd($sock, 'stop');
			--$ckretry;
		}
		closeMpdSock($sock);

		// shutdown if requested
		if ($_SESSION['ckradshutdn'] == "Yes") {
			sysCmd('poweroff');
		}
	}
}

function updPlayHistory() {
	$sock = openMpdSock('localhost', 6600);
	$song = parseCurrentSong($sock);
	closeMpdSock($sock);
	
	// itunes aac file
	if (isset($song['Name']) && getFileExt($song['file']) == 'm4a') {
		$artist = isset($song['Artist']) ? $song['Artist'] : 'Unknown artist';
		$title = $song['Name']; 
		$album = isset($song['Album']) ? $song['Album'] : 'Unknown album';
		
		// search string
		if ($artist == 'Unknown artist' && $album == 'Unknown album') {$searchstr = $title;}
		else if ($artist == 'Unknown artist') {$searchstr = $album . '+' . $title;}
		else if ($album == 'Unknown album') {$searchstr = $artist . '+' . $title;}
		else {$searchstr = $artist . '+' . $album;}

	// radio station
	} else if (isset($song['Name']) || (substr($song['file'], 0, 4) == 'http' && !isset($song['Artist']))) {
		$artist = 'Radio station';

		if (!isset($song['Title']) || trim($song['Title']) == '') {
			$title = $song['file'];
		} else {
			// use custom name if indicated
			$title = $_SESSION[$song['file']]['name'] == 'Classic And Jazz' ? 'CLASSIC & JAZZ (Paris - France)' : $song['Title'];
		}
		
		if (isset($_SESSION[$song['file']])) {
			$album = $_SESSION[$song['file']]['name'];
		} else {
			$album = isset($song['Name']) ? $song['Name'] : 'Unknown station';
		}
		
		// search string
		if ($title != 'Streaming source') {
			$searchstr = str_replace('-', ' ', $title);
			$searchstr = str_replace('&', ' ', $searchstr);
			$searchstr = preg_replace('!\s+!', '+', $searchstr);
		}
		
	// song file or upnp url	
	} else {
		$artist = isset($song['Artist']) ? $song['Artist'] : 'Unknown artist';
		$title = isset($song['Title']) ? $song['Title'] : pathinfo(basename($song['file']), PATHINFO_FILENAME);
		$album = isset($song['Album']) ? $song['Album'] : 'Unknown album';

		// search string
		if ($artist == 'Unknown artist' && $album == 'Unknown album') {$searchstr = $title;}
		else if ($artist == 'Unknown artist') {$searchstr = $album . '+' . $title;}
		else if ($album == 'Unknown album') {$searchstr = $artist . '+' . $title;}
		else {$searchstr = $artist . '+' . $album;}
	}

	// search url
	if ($title == 'Streaming source') {
		$searchurl = '<span class="playhistory-link"><i class="icon-external-link"></i></span>';
	} else {
		$searcheng = 'http://www.google.com/search?q=';
		$searchurl = '<a href="' . $searcheng . $searchstr . '" class="playhistory-link" target="_blank"><i class="icon-external-link-sign"></i></a>';
	}
	
	// update playback history log
	if ($title != '' && $title != $_SESSION['phistsong']) {
		$_SESSION['phistsong'] = $title; // store title as-is
		cfgdb_update('cfg_engine', cfgdb_connect(), 'phistsong', str_replace("'", "''", $title)); // write to cfg db using sql escaped single quotes

		$historyitem = '<li class="playhistory-item"><div>' . date('Y-m-d H:i') . $searchurl . $title . '</div><span>' . $artist . ' - ' . $album . '</span></li>';
		$result = updPlayHist($historyitem);
	}
}

function runQueuedJob() {
	$_SESSION['w_lock'] = 1;
	workerLog('worker: Job ' . $_SESSION['w_queue']);
	
	switch($_SESSION['w_queue']) {
		// src-config jobs
		case 'updmpddb':
			// clear libcache
			sysCmd('truncate /var/www/libcache.json --size 0');
			// db update
			$sock = openMpdSock('localhost', 6600);
			sendMpdCmd($sock, 'update');
			closeMpdSock($sock);
			break;
		case 'sourcecfg':
			// clear libcache
			sysCmd('truncate /var/www/libcache.json --size 0');
			// update cfg_source and do the mounts, waitworker() handles the db update
			wrk_sourcecfg($_SESSION['w_queueargs']);
			break;
		
		// mpd-config jobs
		case 'mpdrestart':
			sysCmd('systemctl restart mpd');
			break;
		case 'mpdcfg':
			// stop playback
			sysCmd('mpc stop');
			
			// update config file
			wrk_mpdconf($_SESSION['i2sdevice']);

			// set hardware volume to 0dB (100) if mpd software or disabled and hdwr vol controller exists
			if (($_SESSION['mpdmixer'] == 'software' || $_SESSION['mpdmixer'] == 'disabled') && $_SESSION['alsavolume'] != 'none') {
				sysCmd('/var/www/command/util.sh set-alsavol ' . '"' . $_SESSION['amixname']  . '"' . ' 100');
			}

			// restart mpd here so it picks up mpd.conf changes
			sysCmd('systemctl restart mpd');

			sleep(1);

			// set knob and mpd/hardware volume to 0
			sysCmd('/var/www/vol.sh 0');

			// restart shairport-sync if device num changed
			if ($_SESSION['w_queueargs'] == 'restartsps' && $_SESSION['airplaysvc'] == 1) {
				sysCmd('killall shairport-sync');
				sysCmd('rm /tmp/shairport-sync-metadata');
				startSps();
			}
			break;

		// squeezelite jobs
		case 'slsvc':
			if ($_SESSION['slsvc'] == '1') {
				sysCmd('systemctl start squeezelite-' . $_SESSION['procarch']);
			}
			else {
				sysCmd('killall -s 9 squeezelite-' . $_SESSION['procarch']);
			}
			break;
		case 'slrestart':
			slRestart();
			break;
		case 'slcfgupdate':
			cfgSqueezelite();
			if ($_SESSION['slsvc'] == '1') {
				slRestart();
			}
			break;
			
		// net-config jobs
		case 'netcfg':
			// configure network interfaces
			cfgNetIfaces();

			// reset dhcpcd conf
			sysCmd('sed -i "/interface wlan0/d" /etc/dhcpcd.conf');
			sysCmd('sed -i "/static ip_address/d" /etc/dhcpcd.conf');
			sysCmd('echo "#interface wlan0" >> /etc/dhcpcd.conf');
			sysCmd('echo "#static ip_address=172.24.1.1/24" >> /etc/dhcpcd.conf');

			// configure hostapd conf
			cfgHostApd();
			
			// since the files have changed
			sysCmd('systemctl daemon-reload');

			workerLog('worker: Network config ' . ($_SESSION['w_queueargs'] == 'reset' ? 'reset to eth0 and DHCP' : 'changed'));
			break;

		// snd-config jobs

		case 'i2sdevice':
			cfgI2sOverlay($_SESSION['w_queueargs']);
			break;
		case 'alsavolume':
			$mixername = getMixerName($_SESSION['i2sdevice']);
			sysCmd('/var/www/command/util.sh set-alsavol ' . '"' . $mixername  . '"' . ' ' . $_SESSION['w_queueargs']);
			break;
		case 'rotaryenc':
			$cmd = $_SESSION['w_queueargs'] == 1 ? '/usr/local/bin/rotenc > /dev/null 2>&1 &' : 'killall /usr/local/bin/rotenc > /dev/null 2>&1 &';
			sysCmd($cmd);
			break;
		case 'crossfeed':
			sysCmd('mpc stop');

			if ($_SESSION['w_queueargs'] == 'disabled') {
				sysCmd('mpc enable only 1');
			}
			else {
				sysCmd('sed -i "/controls/c\ \t\t\tcontrols [ ' . $_SESSION['w_queueargs'] . ' ]"' . ' /usr/share/alsa/alsa.conf.d/crossfeed.conf');
				sysCmd('mpc enable only 2');
			}
			break;
		case 'mpdassvc':
			sysCmd('killall mpdas > /dev/null 2>&1 &');
			cfgAudioScrobbler();
			if ($_SESSION['w_queueargs'] == 1) {
				sysCmd('/usr/local/bin/mpdas > /dev/null 2>&1 &');
			}
			break;
		case 'mpdcrossfade':
			sysCmd('mpc crossfade ' . $_SESSION['w_queueargs']);
			break;
		case 'airplaysvc':
			sysCmd('killall shairport-sync');
			sysCmd('rm /tmp/shairport-sync-metadata');
			playerSession('write', 'airplayactv', '0');
			if ($_SESSION['airplaysvc'] == 1) {startSps();}
			break;
		case 'upnpsvc':
			sysCmd('/var/www/command/util.sh chg-name upnp ' . $_SESSION['w_queueargs']);
			sysCmd('systemctl stop upmpdcli');
			if ($_SESSION['upnpsvc'] == 1) {sysCmd('systemctl start upmpdcli');}
			break;
		case 'minidlna':
			sysCmd('/var/www/command/util.sh chg-name dlna ' . $_SESSION['w_queueargs']);
			sysCmd('systemctl stop minidlna');
			if ($_SESSION['dlnasvc'] == 1) {
				startMiniDlna();
			} else {
				syscmd('rm -r /var/lib/minidlna/* > /dev/null 2>&1 &');
				sysCmd('umount /mnt/UPNP > /dev/null 2>&1 &');
			}
			break;
		case 'dlnarebuild':
			sysCmd('systemctl stop minidlna');
			syscmd('rm -r /var/lib/minidlna/* > /dev/null 2>&1 &');
			sysCmd('umount /mnt/UPNP > /dev/null 2>&1 &');
			sleep(2);
			startMiniDlna();
			break;

		// sys-config jobs

		case 'installupd':
			sysCmd('/var/www/command/updater.sh ' . getPkgId() . ' > /dev/null 2>&1');
			break;
		case 'timezone':
			sysCmd('/var/www/command/util.sh set-timezone ' . $_SESSION['w_queueargs']);
			break;
		case 'hostname':
			sysCmd('/var/www/command/util.sh chg-name host ' . $_SESSION['w_queueargs']);
			break;
		case 'browsertitle':
			sysCmd('/var/www/command/util.sh chg-name browsertitle ' . $_SESSION['w_queueargs']);
			break;
		case 'install-kernel':
			$cmd = '/var/www/command/util.sh install-kernel ' . $_SESSION['w_queueargs'];
			$result = sysCmd($cmd);
			workerLog('worker: util.sh install-kernel output (' .  $result[0] . ')');
			workerLog('worker: util.sh install-kernel output (' .  $result[1] . ')');
			workerLog('worker: util.sh install-kernel output (' .  $result[2] . ')');
			// reset overlay
			cfgI2sOverlay($_SESSION['i2sdevice']);
			break;
		case 'cpugov':
			sysCmd('echo "' . $_SESSION['queueargs'] . '" | tee /sys/devices/system/cpu/cpu*/cpufreq/scaling_governor' . ' > /dev/null');
			break;
		case 'mpdsched':
			sysCmd('sed -i "/CPUSchedulingPolicy/c\CPUSchedulingPolicy=' . $_SESSION['w_queueargs'] . '"' . ' /lib/systemd/system/mpd.service');
			sysCmd('systemctl daemon-reload');
			sysCmd('systemctl restart mpd');
			break;
		case 'wifibt':
			if ($_SESSION['w_queueargs'] == 0) {
				disableWifiBt();
			}
			else {
				sysCmd('sudo rm /etc/modprobe.d/wifi-bt.conf');
				sysCmd('systemctl enable bluetooth');
			}
			break;
		case 'hdmiport':
			$cmd = $_SESSION['w_queueargs'] == '1' ? 'tvservice -p' : 'tvservice -o';
			sysCmd($cmd . ' > /dev/null');
			break;
		case 'maxusbcurrent':
			$cmd = $_SESSION['w_queueargs'] == 1 ? 'echo max_usb_current=1 >> /boot/config.txt' : 'sed -i /max_usb_current/d /boot/config.txt';
			sysCmd($cmd);
			break;
		case 'uac2fix':
			$ext = $_SESSION['w_queueargs'] == 1 ? 'uac2fix' : 'default';
			sysCmd('cp /boot/cmdline.txt.' . $ext . ' /boot/cmdline.txt' . ' > /dev/null');
			break;
		case 'lcdup':
			$_SESSION['w_queueargs'] == 1 ? startLcdUpdater() : sysCmd('killall inotifywait > /dev/null 2>&1 &');
			break;
		case 'clearsyslogs':
			sysCmd('/var/www/command/util.sh clear-syslogs');
			break;
		case 'clearplayhistory':
			sysCmd('/var/www/command/util.sh clear-playhistory');
			break;
		case 'compactdb':
			sysCmd('sqlite3 /var/www/db/player.db "vacuum"');
			break;
		case 'expandsdcard':
			sysCmd('/var/www/command/resizefs.sh start');
			sleep(3); // so message appears on sys-config screen before reboot happens
			sysCmd('mpc stop && reboot');
			break;
		case 'nettime': // not working...
			sysCmd('systemctl stop ntp');
			sysCmd('ntpd -qgx > /dev/null 2>&1 &');
			sysCmd('systemctl start ntp');
			break;
		case 'keyboard':
			sysCmd('/var/www/command/util.sh set-keyboard ' . $_SESSION['w_queueargs']);
			break;
		case 'kvariant':
			sysCmd('/var/www/command/util.sh set-keyboard-variant' . $_SESSION['w_queueargs']);
			break;  

		// moode jobs

		case 'reboot':
		case 'poweroff':
			sysCmd('/var/www/command/restart.sh ' . $_SESSION['w_queue']);
			break;
		case 'reloadclockradio':
			$GLOBALS['ckstart'] = $_SESSION['ckradstart'];
			$GLOBALS['ckstop'] = $_SESSION['ckradstop'];
			break;
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
	
	// reset job queue
	$_SESSION['w_queue'] = '';
	$_SESSION['w_queueargs'] = '';
	$_SESSION['w_lock'] = 0;
	$_SESSION['w_active'] = 0;
}
