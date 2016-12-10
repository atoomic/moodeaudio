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
 * 2016-11-27 3.0 TC initial version
 * 2016-12-05 3.1 TC fix dlna server status page not found
 *
 */

require_once dirname(__FILE__) . '/inc/playerlib.php';

playerSession('open', '' ,'');

// AUDIO

// i2s device
if (isset($_POST['update_i2s_device'])) {
	if (isset($_POST['i2sdevice'])) {
		submitJob('i2sdevice', $_POST['i2sdevice'], 'I2S audio setting updated', 'Reboot then APPLY mpd config', 6);
		playerSession('write', 'i2sdevice', $_POST['i2sdevice']);
	} 
}

// alsa volume
if (isset($_POST['update_alsa_volume'])) {
	if (isset($_POST['alsavolume'])) {
		submitJob('alsavolume', $_POST['alsavolume'], 'ALSA volume updated', '');
		playerSession('write', 'alsavolume', $_POST['alsavolume']);
	}
}

// rotary encoder
if (isset($_POST['rotaryenc']) && $_POST['rotaryenc'] != $_SESSION['rotaryenc']) {
	$title = $_POST['rotaryenc'] == 1 ? 'Rotary encoder on' : 'Rotary encoder off';
	submitJob('rotaryenc', $_POST['rotaryenc'], $title, '');
	playerSession('write', 'rotaryenc', $_POST['rotaryenc']);
} 

// crossfeed
if (isset($_POST['crossfeed']) && $_POST['crossfeed'] != $_SESSION['crossfeed']) {
	submitJob('crossfeed', $_POST['crossfeed'], 'Crossfeed settings updated', '');
	playerSession('write', 'crossfeed', $_POST['crossfeed']);
}

// auto-shuffle
if (isset($_POST['ashufflesvc']) && $_POST['ashufflesvc'] != $_SESSION['ashufflesvc']) {
	$_SESSION['notify']['title'] = $_POST['ashufflesvc'] == 1 ? 'Auto-shuffle enabled' : 'Auto-shuffle disabled';
	$_SESSION['notify']['duration'] = 3;
	playerSession('write', 'ashufflesvc', $_POST['ashufflesvc']);
	// turn off mpd random play so there is no conflict
	$sock = openMpdSock('localhost', 6600);
	sendMpdCmd($sock, 'random 0');
	$return = readMpdResp($sock);
}

// autoplay last played item after reboot/powerup
if (isset($_POST['autoplay']) && $_POST['autoplay'] != $_SESSION['autoplay']) {
	$_SESSION['notify']['title'] = $_POST['autoplay'] == 1 ? 'Autoplay on' : 'Autoplay off';
	$_SESSION['notify']['duration'] = 3;
	playerSession('write', 'autoplay', $_POST['autoplay']);
}

// mpd crossfade
if (isset($_POST['mpdcrossfade']) && $_POST['mpdcrossfade'] != $_SESSION['mpdcrossfade']) {
	submitJob('mpdcrossfade', $_POST['mpdcrossfade'], 'Crossfade settings updated', '');
	playerSession('write', 'mpdcrossfade', $_POST['mpdcrossfade']);
}

// NETWORK AUDIO

// airplay receiver
if (isset($_POST['update_airplay_settings'])) {
	if (isset($_POST['airplayname']) && $_POST['airplayname'] != $_SESSION['airplayname']) {
		$title = 'Airplay name updated';
		playerSession('write', 'airplayname', $_POST['airplayname']);
	} 

	if (isset($_POST['airplaysvc']) && $_POST['airplaysvc'] != $_SESSION['airplaysvc']) {
		$title = $_POST['airplaysvc'] == 1 ? 'Airplay receiver on' : 'Airplay receiver off';
		playerSession('write', 'airplaysvc', $_POST['airplaysvc']);
	}

	submitJob('airplaysvc', '', $title, '');
}

// airplay metadata
if (isset($_POST['update_airplay_meta'])) {
	if (isset($_POST['airplaymeta']) && $_POST['airplaymeta'] != $_SESSION['airplaymeta']) {
		$title = $_POST['airplaymeta'] == 1 ? 'Airplay metadata on' : 'Airplay metadata off';
		playerSession('write', 'airplaymeta', $_POST['airplaymeta']);
		submitJob('airplaysvc', '', $title, '');
	}
}

// airplay volume
if (isset($_POST['update_airplayvol'])) {
	if (isset($_POST['airplayvol']) && $_POST['airplayvol'] != $_SESSION['airplayvol']) {
		$title = $_POST['airplayvol'] == 'auto' ? 'Airplay volume set to Auto' : 'Airplay volume set to Software';
		playerSession('write', 'airplayvol', $_POST['airplayvol']);
		submitJob('airplaysvc', '', $title, '');
	}
}

// upnp renderer
if (isset($_POST['update_upnp_settings'])) {
	$currentUpnpName = $_SESSION['upnpname'];

	if (isset($_POST['upnpname']) && $_POST['upnpname'] != $_SESSION['upnpname']) {
		$title = 'UPnP name updated';
		playerSession('write', 'upnpname', $_POST['upnpname']);
	}

	if (isset($_POST['upnpsvc']) && $_POST['upnpsvc'] != $_SESSION['upnpsvc']) {
		$title = $_POST['upnpsvc'] == 1 ? 'UPnP renderer on' : 'UPnP renderer off';
		playerSession('write', 'upnpsvc', $_POST['upnpsvc']);
	} 

	submitJob('upnpsvc', '"' . $currentUpnpName . '" ' . '"' . $_POST['upnpname'] . '"', $title, '');
}

// squeezelite renderer
if (isset($_POST['update_sl_settings'])) {
	if (isset($_POST['slsvc']) && $_POST['slsvc'] != $_SESSION['slsvc']) {
		$title = $_POST['slsvc'] == 1 ? 'Squeezelite renderer on' : 'Squeezelite renderer off';
		playerSession('write', 'slsvc', $_POST['slsvc']);
	}

	submitJob('slsvc', '', $title, '');
}

// dlna server
if (isset($_POST['update_dlna_settings'])) {
	$currentDlnaName = $_SESSION['dlnaname'];

	if (isset($_POST['dlnaname']) && $_POST['dlnaname'] != $_SESSION['dlnaname']) {
		$title = 'DLNA name updated';
		playerSession('write', 'dlnaname', $_POST['dlnaname']);
	}

	if (isset($_POST['dlnasvc']) && $_POST['dlnasvc'] != $_SESSION['dlnasvc']) {
		$title = $_POST['dlnasvc'] == 1 ? 'DLNA server on' : 'DLNA server off';
		$msg = $_POST['dlnasvc'] == 1 ? 'DB rebuild initiated' : '';
		playerSession('write', 'dlnasvc', $_POST['dlnasvc']);
	} 

	submitJob('minidlna', '"' . $currentDlnaName . '" ' . '"' . $_POST['dlnaname'] . '"', $title, $msg);
}

// rebuild dlna db
if (isset($_POST['rebuild_dlnadb'])) {
	if ($_SESSION['dlnasvc'] == 1) {
		submitJob('dlnarebuild', '', 'DB rebuild initiated', '');
	} else {
		$_SESSION['notify']['title'] = 'Turn DLNA server on';
		$_SESSION['notify']['msg'] = 'DB rebuild will initiate';
	}
}

// AUDIO SERVICES

// audio scrobbler
if (isset($_POST['update_mpdas'])) {
	if (isset($_POST['mpdasuser']) && $_POST['mpdasuser'] != $_SESSION['mpdasuser']) {
		playerSession('write', 'mpdasuser', $_POST['mpdasuser']);
	}
 
	if (isset($_POST['mpdaspwd']) && $_POST['mpdaspwd'] != $_SESSION['mpdaspwd']) {
		playerSession('write', 'mpdaspwd', $_POST['mpdaspwd']);
	} 

	submitJob('mpdassvc', $_POST['mpdassvc'], 'Scrobbler settings updated', '');
	playerSession('write', 'mpdassvc', $_POST['mpdassvc']);
}

session_write_close();

// AUDIO

// i2s audio device
$result = sdbquery("select name from cfg_audiodev where iface='I2S' and (kernel='' or kernel='" . $_SESSION['kernel'] . "')", cfgdb_connect());
$array = array();
$array[0]['name'] = 'none';
$dacList = array_merge($array, $result);
foreach ($dacList as $dac) {
	$dacName = ($dac['name'] == 'none') ? 'None' : $dac['name'];
	$selected = ($_SESSION['i2sdevice'] == $dac['name']) ? ' selected' : '';
	$_i2s['i2sdevice'] .= sprintf('<option value="%s"%s>%s</option>\n', $dac['name'], $selected, $dacName);
}

// alsa volume
if ($_SESSION['alsavolume'] == 'none') {
	$_alsa_volume = '';
	$_alsa_volume_readonly = 'readonly';
	$_alsa_volume_hide = 'hide';
	$_alsa_volume_msg = "<span class=\"help-block help-block-margin\">Hardware volume controller not detected</span>";
} else {
	$mixername = getMixerName($_SESSION['i2sdevice']);
	// TC there is a visudo config that allows this cmd to be run by www-data, the user context for this page
	$result = sysCmd("/var/www/command/util.sh get-alsavol " . '"' . $mixername . '"');
	$_alsa_volume = str_replace('%', '', $result[0]);
	if (isset($_POST['alsavolume']) && $_alsa_volume != $_POST['alsavolume']) { // worker has not processed the change yet
		$_alsa_volume = $_POST['alsavolume'];
	}
	$_alsa_volume_readonly = '';
	$_alsa_volume_hide = '';
	$_alsa_volume_msg = '';
}

// rotary encoder
$_system_select['rotaryenc1'] .= "<input type=\"radio\" name=\"rotaryenc\" id=\"togglerotaryenc1\" value=\"1\" " . (($_SESSION['rotaryenc'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['rotaryenc0'] .= "<input type=\"radio\" name=\"rotaryenc\" id=\"togglerotaryenc2\" value=\"0\" " . (($_SESSION['rotaryenc'] == 0) ? "checked=\"checked\"" : "") . ">\n";

// crossfeed
$_system_select['crossfeed'] .= "<option value=\"disabled\" " . (($_SESSION['crossfeed'] == 'disabled' OR $_SESSION['crossfeed'] == '') ? "selected" : "") . ">disabled</option>\n";
$_system_select['crossfeed'] .= "<option value=\"700 4.5\" " . (($_SESSION['crossfeed'] == '700 4.5') ? "selected" : "") . ">700 Hz 4.5 dB</option>\n";
$_system_select['crossfeed'] .= "<option value=\"700 6.0\" " . (($_SESSION['crossfeed'] == '700 6.0') ? "selected" : "") . ">700 Hz 6.0 dB</option>\n";
$_system_select['crossfeed'] .= "<option value=\"650 9.5\" " . (($_SESSION['crossfeed'] == '650 9.5') ? "selected" : "") . ">650 Hz 9.5 dB</option>\n";

// auto-shuffle
$_system_select['ashufflesvc1'] .= "<input type=\"radio\" name=\"ashufflesvc\" id=\"toggleashufflesvc1\" value=\"1\" " . (($_SESSION['ashufflesvc'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['ashufflesvc0'] .= "<input type=\"radio\" name=\"ashufflesvc\" id=\"toggleashufflesvc2\" value=\"0\" " . (($_SESSION['ashufflesvc'] == 0) ? "checked=\"checked\"" : "") . ">\n";

// autoplay after start
$_system_select['autoplay1'] .= "<input type=\"radio\" name=\"autoplay\" id=\"toggleautoplay1\" value=\"1\" " . (($_SESSION['autoplay'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['autoplay0'] .= "<input type=\"radio\" name=\"autoplay\" id=\"toggleautoplay2\" value=\"0\" " . (($_SESSION['autoplay'] == 0) ? "checked=\"checked\"" : "") . ">\n";

// mpd crossfade
$_mpdcrossfade = $_SESSION['mpdcrossfade'];

// NETWORK AUDIO

// airplay receiver, metadata and volume
$_system_select['airplaysvc1'] .= "<input type=\"radio\" name=\"airplaysvc\" id=\"toggleairplaysvc1\" value=\"1\" " . (($_SESSION['airplaysvc'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['airplaysvc0'] .= "<input type=\"radio\" name=\"airplaysvc\" id=\"toggleairplaysvc2\" value=\"0\" " . (($_SESSION['airplaysvc'] == 0) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['airplayname'] = $_SESSION['airplayname'];
$_system_select['airplaymeta1'] .= "<input type=\"radio\" name=\"airplaymeta\" id=\"toggleairplaymeta1\" value=\"1\" " . (($_SESSION['airplaymeta'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['airplaymeta0'] .= "<input type=\"radio\" name=\"airplaymeta\" id=\"toggleairplaymeta2\" value=\"0\" " . (($_SESSION['airplaymeta'] == 0) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['airplayvol'] .= "<option value=\"auto\" " . (($_SESSION['airplayvol'] == 'auto') ? "selected" : "") . ">Auto</option>\n";
$_system_select['airplayvol'] .= "<option value=\"software\" " . (($_SESSION['airplayvol'] == 'software') ? "selected" : "") . ">Software</option>\n";

// upnp renderer
$_system_select['upnpsvc1'] .= "<input type=\"radio\" name=\"upnpsvc\" id=\"toggleupnpsvc1\" value=\"1\" " . (($_SESSION['upnpsvc'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['upnpsvc0'] .= "<input type=\"radio\" name=\"upnpsvc\" id=\"toggleupnpsvc2\" value=\"0\" " . (($_SESSION['upnpsvc'] == 0) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['upnpname'] = $_SESSION['upnpname'];

// squeezelite renderer
$_system_select['slsvc1'] .= "<input type=\"radio\" name=\"slsvc\" id=\"toggleslsvc1\" value=\"1\" " . (($_SESSION['slsvc'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['slsvc0'] .= "<input type=\"radio\" name=\"slsvc\" id=\"toggleslsvc2\" value=\"0\" " . (($_SESSION['slsvc'] == 0) ? "checked=\"checked\"" : "") . ">\n";

// dlna server
$_system_select['dlnasvc1'] .= "<input type=\"radio\" name=\"dlnasvc\" id=\"toggledlnasvc1\" value=\"1\" " . (($_SESSION['dlnasvc'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['dlnasvc0'] .= "<input type=\"radio\" name=\"dlnasvc\" id=\"toggledlnasvc2\" value=\"0\" " . (($_SESSION['dlnasvc'] == 0) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['dlnaname'] = $_SESSION['dlnaname'];
$_system_select['hostname'] = $_SESSION['hostname'];

// AUDIO SERVICES

// audio scrobbler
$_system_select['mpdassvc1'] .= "<input type=\"radio\" name=\"mpdassvc\" id=\"togglempdassvc1\" value=\"1\" " . (($_SESSION['mpdassvc'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['mpdassvc0'] .= "<input type=\"radio\" name=\"mpdassvc\" id=\"togglempdassvc2\" value=\"0\" " . (($_SESSION['mpdassvc'] == 0) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['mpdasuser'] = $_SESSION['mpdasuser'];
$_system_select['mpdaspwd'] = $_SESSION['mpdaspwd'];

$section = basename(__FILE__, '.php');

waitWorker(1, 'snd-config');

$tpl = "snd-config.html";
include('header.php'); 
eval("echoTemplate(\"" . getTemplate("templates/$tpl") . "\");");
include('footer.php');
