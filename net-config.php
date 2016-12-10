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
 * Moode Audio Player (C) 2014 Tim Curtis
 * http://moodeaudio.org
 *
 * 2016-06-07 2.6 TC network config
 * 2016-08-28 2.7 TC clean up wording on reboot messages
 *
 */
 
require_once dirname(__FILE__) . '/inc/playerlib.php';

playerSession('open', '' ,''); 
$dbh = cfgdb_connect();
//session_write_close();

// reset eth0 and wlan0 to defaults
if (isset($_POST['reset']) && $_POST['reset'] == 1) {
	$value = array('method' => 'dhcp', 'ipaddr' => '', 'netmask' => '', 'gateway' => '', 'pridns' => '', 'secdns' => '', 'wlanssid' => '', 'wlansec' => '', 'wlanpwd' => '');
	cfgdb_update('cfg_network', $dbh, 'eth0', $value);
	cfgdb_update('cfg_network', $dbh, 'wlan0', $value);

	// submit job
	submitJob('netcfg', 'reset', 'Network config reset', 'Reboot required');

// update eth0 and wlan0
} else if (isset($_POST['apply']) && $_POST['apply'] == 1) {	
	$value = array('method' => $_POST['eth0method'], 'ipaddr' => $_POST['eth0ipaddr'], 'netmask' => $_POST['eth0netmask'], 'gateway' => $_POST['eth0gateway'], 'pridns' => $_POST['eth0pridns'], 'secdns' => $_POST['eth0secdns'], 'wlanssid' => '', 'wlansec' => '', 'wlanpwd' => '');
	cfgdb_update('cfg_network', $dbh, 'eth0', $value);
	$value = array('method' => $_POST['wlan0method'], 'ipaddr' => $_POST['wlan0ipaddr'], 'netmask' => $_POST['wlan0netmask'], 'gateway' => $_POST['wlan0gateway'], 'pridns' => $_POST['wlan0pridns'], 'secdns' => $_POST['wlan0secdns'], 'wlanssid' => $_POST['wlan0ssid'], 'wlansec' => $_POST['wlan0sec'], 'wlanpwd' => $_POST['wlan0pwd']);
	cfgdb_update('cfg_network', $dbh, 'wlan0', $value);

	playerSession('write', 'apdssid', $_POST['wlan0apdssid']);
	playerSession('write', 'apdchan', $_POST['wlan0apdchan']);
	playerSession('write', 'apdpwd', $_POST['wlan0apdpwd']);

	// submit job
	submitJob('netcfg', 'apply', 'Network config changed', 'Reboot required');
}

// get current settings: [0] = eth0, [1] = wlan0
$netcfg = sdbquery('select * from cfg_network', $dbh);

// populate form fields

// ETH0
$_eth0method .= "<option value=\"dhcp\" "   . ($netcfg[0]['method'] == 'dhcp' ? 'selected' : '') . " >DHCP</option>\n";
$_eth0method .= "<option value=\"static\" " . ($netcfg[0]['method'] == 'static' ? 'selected' : '') . " >STATIC</option>\n";

// display ipaddr or message 
$ipaddr = sysCmd("ip addr list eth0 |grep \"inet \" |cut -d' ' -f6|cut -d/ -f1");
$_eth0currentip = empty($ipaddr[0]) ? 'Not in use' : $ipaddr[0];

// static ip
$_eth0ipaddr = $netcfg[0]['ipaddr'];
$_eth0netmask = $netcfg[0]['netmask'];
$_eth0gateway = $netcfg[0]['gateway'];
$_eth0pridns = $netcfg[0]['pridns'];
$_eth0secdns = $netcfg[0]['secdns'];

// WLAN0
$_wlan0method .= "<option value=\"dhcp\" "   . ($netcfg[1]['method'] == 'dhcp' ? 'selected' : '') . " >DHCP</option>\n";
$_wlan0method .= "<option value=\"static\" " . ($netcfg[1]['method'] == 'static' ? 'selected' : '') . " >STATIC</option>\n";

// get ipaddr if any
$ipaddr = sysCmd("ip addr list wlan0 |grep \"inet \" |cut -d' ' -f6|cut -d/ -f1");

// derive signal quality
if (!empty($ipaddr[0])) {
	$signal = sysCmd('iwconfig wlan0 | grep -i quality');
	
	$array = explode('=', $signal[0]);
	$leveldbm = $array[2];
	$array = explode(' ', $array[1]);
	$array = explode('/', $array[0]);
	$quality = round((100 * $array[0]) / $array[1]);
}

// determine message to display
if ($_SESSION['apactivated'] == true) {
	$_wlan0currentip = empty($ipaddr[0]) ? 'Unable to activate AP mode' : $ipaddr[0] . ' - AP mode active';
} else {
	$_wlan0currentip = empty($ipaddr[0]) ? 'Not in use' : $ipaddr[0] . ' - signal ' . $quality . '%';
}

// ssid config
$_wlan0ssid = $netcfg[1]['wlanssid'];
$_wlan0sec .= "<option value=\"wpa\"" . ($netcfg[1]['wlansec'] == 'wpa' ? 'selected' : '') . ">WPA/WPA2 Personal</option>\n";
//$_wlan0sec .= "<option value=\"wep\"" . ($netcfg[1]['wlansec'] == 'wep' ? 'selected' : '') . ">WEP</option>\n";
$_wlan0sec .= "<option value=\"none\"" . ($netcfg[1]['wlansec'] == 'none' ? 'selected' : '') . ">No security</option>\n";
$_wlan0pwd = $netcfg[1]['wlanpwd'];

// static ip
$_wlan0ipaddr = $netcfg[1]['ipaddr'];
$_wlan0netmask = $netcfg[1]['netmask'];
$_wlan0gateway = $netcfg[1]['gateway'];
$_wlan0pridns = $netcfg[1]['pridns'];
$_wlan0secdns = $netcfg[1]['secdns'];

// access point
$_wlan0apdssid = $_SESSION['apdssid']; 
$_wlan0apdchan = $_SESSION['apdchan']; 
$_wlan0apdpwd = $_SESSION['apdpwd']; 

session_write_close();

// render page
$section = basename(__FILE__, '.php');

waitWorker(1, 'net-config');	

$tpl = "net-config.html";
include('header.php');
eval("echoTemplate(\"" . getTemplate("templates/$tpl") . "\");");
include('footer.php');
