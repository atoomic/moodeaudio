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
 * - in-place updater
 * - add code to set keyboard and varient: Richard Parslow
 * - add crossfeed setting
 * - add wifi bt setting
 * - remove i2s, alsavolume check and $_rotary_enc_hide for show/hide rotary encoder control
 * 2016-11-27 3.0 TC move audio settings to dedicated page
 * 2016-12-05 3.1 TC add RR to MPD scheduler policy
 *
 */

require_once dirname(__FILE__) . '/inc/playerlib.php';
require_once dirname(__FILE__) . '/inc/timezone.php';
require_once dirname(__FILE__) . '/inc/keyboard.php';

playerSession('open', '' ,'');

// SOFTWARE UPDATE

// check for software update
if (isset($_GET['cmd']) && $_GET['cmd'] == 'checkfor_update') {
	$available = checkForUpd('http://moodeaudio.org/downloads/');
	$lastinstall = checkForUpd('/var/www/');

	// up to date
	if ($available['pkgdate'] == $lastinstall['pkgdate']) {
		$_available_upd = 'Moode software is up to date<br>';
	}
	else {
		// available
		$_available_upd .= '<u><em>Available</u></em><br>';
		$_available_upd .= $available['pkgdate'] == 'None' ? $available['pkgdate'] . '<br>' : 'Package date: ' . $available['pkgdate'] . 
			'<button class="btn btn-primary btn-small set-button" type="submit" name="install_update" value="1">Install</button>' .
			'<button class="btn btn-primary btn-small set-button" data-toggle="modal" href="#view-pkgcontent">View</button><br>';

		$_pkg_description = $available['pkgdesc'];
		$cnt = $available['linecnt'];
		for ($i = 1; $i <= $cnt; $i++) {
			$_pkg_content .= '<li>' . $available[$i] . '</li>';
		}

		// last installed
		$_lastinstall_upd .= '<u><em>Last installed</u></em><br>'; 
		$_lastinstall_upd .= $lastinstall['pkgdate'] == 'None' ? $lastinstall['pkgdate'] : 'Package date: ' . $lastinstall['pkgdate'];
	}
}

// install software update
if (isset($_POST['install_update'])) {
	if ($_POST['install_update'] == 1) {
		submitJob('installupd', '', 'Software update installed', 'Reboot required', 6);
		$_available_upd = 'Moode software is up to date';
		$_lastinstall_upd = '';
	}
}

// GEBERAL

// timezone
if (isset($_POST['update_time_zone'])) {
	if (isset($_POST['timezone']) && $_POST['timezone'] != $_SESSION['timezone']) {
		submitJob('timezone', $_POST['timezone'], 'Timezone set to ' . $_POST['timezone'], '');
		playerSession('write', 'timezone', $_POST['timezone']);
	} 
}

// host name
if (isset($_POST['update_host_name'])) {
	if (isset($_POST['hostname']) && $_POST['hostname'] != $_SESSION['hostname']) {
		if (preg_match("/[^A-Za-z0-9-]/", $_POST['hostname']) == 1) {
			$_SESSION['notify']['title'] = 'Invalid input';
			$_SESSION['notify']['msg'] = "Host name can only contain A-Z, a-z, 0-9 or hyphen (-).";
			$_SESSION['notify']['duration'] = 3;
		} else {
			submitJob('hostname', '"' . $_SESSION['hostname'] . '" ' . '"' . $_POST['hostname'] . '"', 'Host name changed', 'Reboot required');
			playerSession('write', 'hostname', $_POST['hostname']);
		}
	}
}

// browser title
if (isset($_POST['update_browser_title'])) {
	if (isset($_POST['browsertitle']) && $_POST['browsertitle'] != $_SESSION['browsertitle']) {
		submitJob('browsertitle', '"' . $_SESSION['browsertitle'] . '" ' . '"' . $_POST['browsertitle'] . '"', 'Browser title changed', 'Reboot required');
		playerSession('write', 'browsertitle', $_POST['browsertitle']);
	} 
}

// SYSTEM MODIFICATIONS

// linux kernel
if (isset($_POST['update_kernel'])) {
	// update sql table first
	playerSession('write', 'kernel', $_POST['kernel']);
	submitJob('install-kernel', $_POST['kernel'], 'Kernel install complete', 'Reboot required', 6);
} 

// cpu governor
if (isset($_POST['update_cpugov'])) {
	submitJob('cpugov', $_POST['cpugov'], 'CPU governor updated', '');
	playerSession('write', 'cpugov', $_POST['cpugov']);
} 

// mpd scheduler policy
if (isset($_POST['update_mpdsched'])) {
	submitJob('mpdsched', $_POST['mpdsched'], 'MPD scheduler policy updated', 'MPD restarted');
	playerSession('write', 'mpdsched', $_POST['mpdsched']);
} 

// Pi-3 WiFi Bluetooth adapter 
if (isset($_POST['wifibt']) && $_POST['wifibt'] != $_SESSION['wifibt']) {
	$title = $_POST['wifibt'] == 1 ? 'WiFi BT adapter on' : 'WiFi BT adapter off';
	submitJob('wifibt', $_POST['wifibt'], $title, 'Reboot required');
	playerSession('write', 'wifibt', $_POST['wifibt']);
}

// HDMI port
if (isset($_POST['hdmiport']) && $_POST['hdmiport'] != $_SESSION['hdmiport']) {
	$title = $_POST['hdmiport'] == 1 ? 'HDMI port on' : 'HDMI port off';
	submitJob('hdmiport', $_POST['hdmiport'], $title, '');
	playerSession('write', 'hdmiport', $_POST['hdmiport']);
}

// eth0 check
if (isset($_POST['eth0chk']) && $_POST['eth0chk'] != $_SESSION['eth0chk']) {
	$_SESSION['notify']['title'] = $_POST['eth0chk'] == 1 ? 'Eth0 IP check enabled' : 'Eth0 IP check disabled';
	$_SESSION['notify']['msg'] = 'Reboot required';
	playerSession('write', 'eth0chk', $_POST['eth0chk']);
}

// set USB curent to 2X (1200 mA)
if (isset($_POST['maxusbcurrent']) && $_POST['maxusbcurrent'] != $_SESSION['maxusbcurrent']) {
	$title = $_POST['maxusbcurrent'] == 1 ? 'USB current 2x on' : 'USB current 2x off';
	submitJob('maxusbcurrent', $_POST['maxusbcurrent'], $title, 'Reboot required');
	playerSession('write', 'maxusbcurrent', $_POST['maxusbcurrent']);
}

// uac2 fix
if (isset($_POST['update_uac2fix'])) {
	if (isset($_POST['uac2fix']) && $_POST['uac2fix'] != $_SESSION['uac2fix']) {
		$title = $_POST['uac2fix'] == 1 ? 'USB(UAC2) fix enabled' : 'USB(UAC2) fix disabled';
		submitJob('uac2fix', $_POST['uac2fix'], $title, 'Reboot required');
		playerSession('write', 'uac2fix', $_POST['uac2fix']);
	} 
}

// LOCAL SERVICES

// metadata for external apps
if (isset($_POST['extmeta']) && $_POST['extmeta'] != $_SESSION['extmeta']) {
	$_SESSION['notify']['title'] = $_POST['extmeta'] == 1 ? 'Metadata file on' : 'Metadata file off';
	$_SESSION['notify']['duration'] = 3;
	playerSession('write', 'extmeta', $_POST['extmeta']);
}

// lcd updater
if (isset($_POST['update_lcdup'])) {
	if (isset($_POST['lcdupscript']) && $_POST['lcdupscript'] != $_SESSION['lcdupscript']) {
		$_SESSION['notify']['title'] = 'Script path updated';
		$_SESSION['notify']['duration'] = 3;
		playerSession('write', 'lcdupscript', $_POST['lcdupscript']);
	} 

	if (isset($_POST['lcdup']) && $_POST['lcdup'] != $_SESSION['lcdup']) {
		$title = $_POST['lcdup'] == 1 ? 'LCD update engine on' : 'LCD update engine off';
		submitJob('lcdup', $_POST['lcdup'], $title, '');
		playerSession('write', 'lcdup', $_POST['lcdup']);
		playerSession('write', 'extmeta', '1'); // turn on external metadata generation
	} 
}

// MAINTENANCE

// clear system logs
if (isset($_POST['update_clear_syslogs'])) {
	if ($_POST['clearsyslogs'] == 1) {
		submitJob('clearsyslogs', '', 'System logs cleared', '');
	}
}

// clear play history log
if (isset($_POST['update_clear_playhistory'])) {
	if ($_POST['clearplayhistory'] == 1) {
		submitJob('clearplayhistory', '', 'Play history log cleared', '');
	}
}

// compact sqlite database
if (isset($_POST['update_compactdb'])) {
	if ($_POST['compactdb'] == 1) {
		submitJob('compactdb', '', 'SQlite database has been compacted', '');
	}
}

// expand SD card storage
if (isset($_POST['update_expand_sdcard'])) {
	if ($_POST['expandsdcard'] == 1) {
		submitJob('expandsdcard', '', '', '');
		$_SESSION['notify']['title'] = 'Expansion job submitted';
		$_SESSION['notify']['msg'] = 'Reboot initiated';
		$_SESSION['notify']['duration'] = 6;
	}
}

// network time...currently not working
if (isset($_POST['update_nettime'])) {
	if ($_POST['nettime'] == 1) {
		submitJob('nettime', '', 'Fetching network time', '');
	}
}

// debug logging
if (isset($_POST['debuglog']) && $_POST['debuglog'] != $_SESSION['debuglog']) {
	$_SESSION['notify']['title'] = $_POST['debuglog'] == 1 ? 'Debug logging on' : 'Debug logging off';
	$_SESSION['notify']['duration'] = 3;
	playerSession('write', 'debuglog', $_POST['debuglog']);
}

// PERIPHERALS

// keyboard 
if (isset($_POST['update_keyboard'])) {
    if (isset($_POST['keyboard']) && $_POST['keyboard'] != $_SESSION['keyboard']) {
        submitJob('keyboard', $_POST['keyboard'], 'Keyboard set to ' . $_POST['keyboard'], '');
        playerSession('write', 'keyboard', $_POST['keyboard']);
    } 
}

// keyboard variant
if (isset($_POST['update_keyboard_variant'])) {
    if (isset($_POST['kvariant']) && $_POST['kvariant'] != $_SESSION['kvariant']) {
        submitJob('kvariant', $_POST['kvariant'], 'Layout set to ' . $_POST['kvariant'], '');
        playerSession('write', 'kvariant', $_POST['kvariant']);
    } 
}

session_write_close();

// GEBERAL

$_timezone['timezone'] = buildTimezoneSelect($_SESSION['timezone']);
$_system_select['hostname'] = $_SESSION['hostname'];
$_system_select['browsertitle'] = $_SESSION['browsertitle'];

// SYSTEM MODIFICATIONS

// linux kernel
$_system_select['kernel'] .= "<option value=\"Standard\" " . (($_SESSION['kernel'] == 'Standard') ? "selected" : "") . ">Standard</option>\n";
if ($_SESSION['procarch'] == 'armv7l') {
	$_system_select['kernel'] .= "<option value=\"Advanced\" " . (($_SESSION['kernel'] == 'Advanced') ? "selected" : "") . ">Advanced</option>\n";
}

// cpu governor
$_system_select['cpugov'] .= "<option value=\"ondemand\" " . (($_SESSION['cpugov'] == 'ondemand') ? "selected" : "") . ">On-demand</option>\n";
$_system_select['cpugov'] .= "<option value=\"performance\" " . (($_SESSION['cpugov'] == 'performance') ? "selected" : "") . ">Performance</option>\n";

// mpd scheduler policy
$_system_select['mpdsched'] .= "<option value=\"other\" " . (($_SESSION['mpdsched'] == 'other') ? "selected" : "") . ">TS</option>\n";
$_system_select['mpdsched'] .= "<option value=\"fifo\" " . (($_SESSION['mpdsched'] == 'fifo') ? "selected" : "") . ">FIFO</option>\n";
$_system_select['mpdsched'] .= "<option value=\"rr\" " . (($_SESSION['mpdsched'] == 'rr') ? "selected" : "") . ">RR</option>\n";

// wifi bt 
if ($_SESSION['hdwrrev'] == 'Pi-3B 1GB') {
	$_wifibt_hide = '';
	$_system_select['wifibt1'] .= "<input type=\"radio\" name=\"wifibt\" id=\"togglewifibt1\" value=\"1\" " . (($_SESSION['wifibt'] == 1) ? "checked=\"checked\"" : "") . ">\n";
	$_system_select['wifibt0'] .= "<input type=\"radio\" name=\"wifibt\" id=\"togglewifibt2\" value=\"0\" " . (($_SESSION['wifibt'] == 0) ? "checked=\"checked\"" : "") . ">\n";
}
else {
	$_wifibt_hide = 'hide';
}

// hdmi port
$_system_select['hdmiport1'] .= "<input type=\"radio\" name=\"hdmiport\" id=\"togglehdmiport1\" value=\"1\" " . (($_SESSION['hdmiport'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['hdmiport0'] .= "<input type=\"radio\" name=\"hdmiport\" id=\"togglehdmiport2\" value=\"0\" " . (($_SESSION['hdmiport'] == 0) ? "checked=\"checked\"" : "") . ">\n";

// eth0 check
$_system_select['eth0chk1'] .= "<input type=\"radio\" name=\"eth0chk\" id=\"toggleeth0chk1\" value=\"1\" " . (($_SESSION['eth0chk'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['eth0chk0'] .= "<input type=\"radio\" name=\"eth0chk\" id=\"toggleeth0chk2\" value=\"0\" " . (($_SESSION['eth0chk'] == 0) ? "checked=\"checked\"" : "") . ">\n";

// max usb current 2x
$_system_select['maxusbcurrent1'] .= "<input type=\"radio\" name=\"maxusbcurrent\" id=\"togglemaxusbcurrent1\" value=\"1\" " . (($_SESSION['maxusbcurrent'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['maxusbcurrent0'] .= "<input type=\"radio\" name=\"maxusbcurrent\" id=\"togglemaxusbcurrent2\" value=\"0\" " . (($_SESSION['maxusbcurrent'] == 0) ? "checked=\"checked\"" : "") . ">\n";

// uac2 fix
$_system_select['uac2fix1'] .= "<input type=\"radio\" name=\"uac2fix\" id=\"toggleuac2fix1\" value=\"1\" " . (($_SESSION['uac2fix'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['uac2fix0'] .= "<input type=\"radio\" name=\"uac2fix\" id=\"toggleuac2fix2\" value=\"0\" " . (($_SESSION['uac2fix'] == 0) ? "checked=\"checked\"" : "") . ">\n";

// LOCAL SERVICES

// metadata file
$_system_select['extmeta1'] .= "<input type=\"radio\" name=\"extmeta\" id=\"toggleextmeta1\" value=\"1\" " . (($_SESSION['extmeta'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['extmeta0'] .= "<input type=\"radio\" name=\"extmeta\" id=\"toggleextmeta2\" value=\"0\" " . (($_SESSION['extmeta'] == 0) ? "checked=\"checked\"" : "") . ">\n";

// lcd updater
$_system_select['lcdup1'] .= "<input type=\"radio\" name=\"lcdup\" id=\"togglelcdup1\" value=\"1\" " . (($_SESSION['lcdup'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['lcdup0'] .= "<input type=\"radio\" name=\"lcdup\" id=\"togglelcdup2\" value=\"0\" " . (($_SESSION['lcdup'] == 0) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['lcdupscript'] = $_SESSION['lcdupscript'];

// MAINTENANCE

// clear syslogs
$_system_select['clearsyslogs1'] .= "<input type=\"radio\" name=\"clearsyslogs\" id=\"toggleclearsyslogs1\" value=\"1\" " . ">\n";
$_system_select['clearsyslogs0'] .= "<input type=\"radio\" name=\"clearsyslogs\" id=\"toggleclearsyslogs2\" value=\"0\" " . "checked=\"checked\"".">\n";

// clear playback history
$_system_select['clearplayhistory1'] .= "<input type=\"radio\" name=\"clearplayhistory\" id=\"toggleclearplayhistory1\" value=\"1\" " . ">\n";
$_system_select['clearplayhistory0'] .= "<input type=\"radio\" name=\"clearplayhistory\" id=\"toggleclearplayhistory2\" value=\"0\" " . "checked=\"checked\"".">\n";

// compact sqlite database
$_system_select['compactdb1'] .= "<input type=\"radio\" name=\"compactdb\" id=\"togglecompactdb1\" value=\"1\" " . ">\n";
$_system_select['compactdb0'] .= "<input type=\"radio\" name=\"compactdb\" id=\"togglecompactdb2\" value=\"0\" " . "checked=\"checked\"".">\n";

// expand sd card storage
$_system_select['expandsdcard1'] .= "<input type=\"radio\" name=\"expandsdcard\" id=\"toggleexpandsdcard1\" value=\"1\" " . ">\n";
$_system_select['expandsdcard0'] .= "<input type=\"radio\" name=\"expandsdcard\" id=\"toggleexpandsdcard2\" value=\"0\" " . "checked=\"checked\"".">\n";

// network time...currently not working
//$_system_select['nettime1'] .= "<input type=\"radio\" name=\"nettime\" id=\"togglenettime1\" value=\"1\" " . ">\n";
//$_system_select['nettime0'] .= "<input type=\"radio\" name=\"nettime\" id=\"togglenettime2\" value=\"0\" " . "checked=\"checked\"".">\n";

// debug logging
$_system_select['debuglog1'] .= "<input type=\"radio\" name=\"debuglog\" id=\"toggledebuglog1\" value=\"1\" " . (($_SESSION['debuglog'] == 1) ? "checked=\"checked\"" : "") . ">\n";
$_system_select['debuglog0'] .= "<input type=\"radio\" name=\"debuglog\" id=\"toggledebuglog2\" value=\"0\" " . (($_SESSION['debuglog'] == 0) ? "checked=\"checked\"" : "") . ">\n";

// PERIPHERALS

$_keyboard['keyboard'] = buildKeyboardSelect($_SESSION['keyboard']);
$_kvariant['kvariant'] = buildKvariantSelect($_SESSION['kvariant']);  

$section = basename(__FILE__, '.php');

// don't wait if job is 'expandsdcard' 
if ($_POST['expandsdcard'] != 1) {
	waitWorker(1, 'sys-config');
}

$tpl = "sys-config.html";
include('header.php'); 
eval("echoTemplate(\"" . getTemplate("templates/$tpl") . "\");");
include('footer.php');
