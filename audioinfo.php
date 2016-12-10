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
 * along with Moode; see the file COPYING.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Moode Audio Player (C) 2014 Tim Curtis
 * http://moodeaudio.org
 *
 * 2014-08-23 1.0 TC initial version
 * 2016-02-27 2.5 TC rewrite for pre-3.0
 * 2016-06-07 2.6 TC moodeOS 1.0
 * 2016-08-28 2.7 TC
 * - pass $song instead of $song['file'] in getEncodedAt()
 * - set encodedaAT to 'Unknown' for Airplay streams
 * - fix volume "disabled" not being displayed
 * 2016-11-27 3.0 TC 
 * - $decoded_to = 'PCM' instead of 'DSD' since native DSD not supported yet
 * - improve chip option handling
 *
 */

require_once dirname(__FILE__) . '/inc/playerlib.php';

$sock = openMpdSock('localhost', 6600);

if (!$sock) {
	die('connection to mpd failed');
} else {
	playerSession('open', '' ,''); 
	$dbh = cfgdb_connect();
	session_write_close();
}

// hardware params
$cardnum = file_get_contents('/proc/asound/card1/id') == '' ? '0' : '1';
$hwparams = parseHwParams(shell_exec('cat /proc/asound/card' . $cardnum . '/pcm0p/sub0/hw_params'));	

// input
if ($_SESSION['airplayactv'] == '1') {
	$file = 'Airplay stream';
	$encoded_at = 'Unknown';
	$decoded_to = '16 bit, 44.1 kHz, Stereo';
	$decode_rate = '';
}
else {
	$song = parseCurrentSong($sock);
	$file = $song['file'];

	// Krishna Simonese: if current file is a UPNP/DLNA url, replace *20 with space
	// NOTE normaly URI would be encoded %20 but ? is changing it to *20
	if ( substr( $file, 0, 4) == 'http' ) {
		$file = str_replace( '*20', ' ', $file );
	}
	
	$encoded_at = getEncodedAt($song, 'verbose');
	$status = parseStatus(getMpdStatus($sock));
	
	if ($hwparams['status'] == 'active') { 		
		// decode bits
		if ($status['audio_sample_depth'] == 'dsd') {
			$decoded_to .= 'PCM, ';
		} else {
			$decoded_to .= $status['audio_sample_depth'] . ' bit, ';
		}
		// decode rate
		$decoded_to .= $status['audio_sample_rate'];
		$decoded_to .= empty($status['audio_sample_rate']) ? '' : ' kHz, ';
		$decoded_to .= $status['audio_channels'];
		// decode bit rate
		$decode_rate .= $status['bitrate'];
	} else {
		$decoded_to = '';
		$decode_rate = '0 bps';
	}
}

// dsp
$mpdconf = parseCfgMpd($dbh);

if ($mpdconf['audio_output_format'] == 'disabled' || $_SESSION['airplayactv'] == '1') {
	$resampler = 'off';
	$resampler_format = '';
} else {
	$resampler = ' (' . $mpdconf['samplerate_converter'] . ')';
	$resampler_format .= $mpdconf['audio_output_depth'];
	$resampler_format .= ' bit, ';
	$resampler_format .= $mpdconf['audio_output_rate'];
	$resampler_format .= ' kHz, ';
	$resampler_format .= $mpdconf['audio_output_chan'];
}

if ($_SESSION['crossfeed'] == 'disabled') {
	$crossfeed = 'off';
}
else {
	$array = explode(' ', $_SESSION['crossfeed']);
	$crossfeed = $array[0] . ' Hz ' . $array[1] . ' dB';
}

// chip options
$result = cfgdb_read('cfg_audiodev', $dbh, $_SESSION['i2sdevice']);
$chips = array('Burr Brown PCM5242','Burr Brown PCM5142','Burr Brown PCM5122','Burr Brown PCM5121','Burr Brown PCM5122 (PCM5121)','Burr Brown TAS5756');
if (in_array($result[0]['dacchip'], $chips) && $result[0]['settings'] != '') {
	$array = explode(',', $result[0]['settings']);

	$aVol = $array[0] === '100' ? '0 dB' : '-6 dB'; // Analog volume
	$aPbb = $array[1] === '100' ? '.8 dB' : '0 dB'; // Analog playback boost
	$dFil = $array[2]; // Digital interpolation filter

	$chip_options = $dFil . ', aVol=' . $aVol . ', aPbb=' . $aPbb;
} else {
	$chip_options = 'none';
}

if ($_SESSION['mpdmixer'] == 'hardware') {
	if ($_SESSION['volcurve'] == 'Yes') {
		$curvetype = 'Logarthmic curve';
		if ($_SESSION['volcurvefac'] == 56) {$curveslope = 'Standard slope,';}
		else if ($_SESSION['volcurvefac'] == 66) {$curveslope = 'Less (-10) slope,';}
		else if ($_SESSION['volcurvefac'] == 76) {$curveslope = 'Less (-20) slope,';}
		else if ($_SESSION['volcurvefac'] == 86) {$curveslope = 'Less (-30) slope,';}
		else if ($_SESSION['volcurvefac'] == 50) {$curveslope = 'More (+06) slope,';}
		else if ($_SESSION['volcurvefac'] == 44) {$curveslope = 'More (+12) slope,';}
		else if ($_SESSION['volcurvefac'] == 38) {$curveslope = 'More (+18) slope,';}
	} else {
		$curvetype = 'Linear';
		$curveslope = '';
	}
	
	$volume = 'Hardware, ' . $curvetype . ', ' . $curveslope . ' Max ' . $_SESSION['volmaxpct'] . '%';
	
} else if ($_SESSION['mpdmixer'] == 'software') {
	$volume = 'Software (MPD 32 bit float with dither)';
} else {
	$volume = $_SESSION['mpdmixer'];
}

// output
if ($hwparams['status'] == 'active') {
	$hwparams_format = $hwparams['format'];
	$hwparams_format .= ' bit, ';
	$hwparams_format .= $hwparams['rate'];
	$hwparams_format .= ' kHz, ';
	$hwparams_format .= $hwparams['channels'];
	$hwparams_calcrate = $hwparams['calcrate'];
	$hwparams_calcrate .= ' mbps';
} else {
	$hwparams_format = '';
	$hwparams_calcrate = '0 bps';
}

// audio device
$result = cfgdb_read('cfg_audiodev', $dbh, $_SESSION['adevname']);
$devname = $_SESSION['adevname'] == 'none' ? '' : $_SESSION['adevname'];
$dacchip = $result[0]['dacchip'];
$devarch = $result[0]['arch'];
$iface = $result[0]['iface'];

// system
$cpuload = shell_exec("top -bn 2 -d 0.5 | grep 'Cpu(s)' | tail -n 1 | awk '{print $2 + $4 + $6}'");
$cpuload = number_format($cpuload,0,'.','');
$cputemp = substr(shell_exec('cat /sys/class/thermal/thermal_zone0/temp'), 0, 2);
$cpufreq = (float)shell_exec('cat /sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq');

if ($cpufreq < 1000000) {
	$cpufreq = number_format($cpufreq / 1000, 0, '.', '');
	$cpufreq .= ' MHz';
} else {
	$cpufreq = number_format($cpufreq / 1000000, 1, '.', '');
	$cpufreq .= ' GHz';
}

$sysarch = trim(shell_exec('uname -m'));

$tpl = 'audioinfo.html';
eval('echoTemplate("' . getTemplate("templates/$tpl") . '");');
