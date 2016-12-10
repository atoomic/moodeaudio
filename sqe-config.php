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
 * 2016-11-27 3.0 TC Squeezelite config
 *
 */
 
require_once dirname(__FILE__) . '/inc/playerlib.php';

playerSession('open', '' ,''); 
$dbh = cfgdb_connect();
session_write_close();

// restart Squeezelite
if (isset($_POST['slrestart']) && $_POST['slrestart'] == 1) {
	submitJob('slrestart', '', 'Squeezelite restarted', '');
}

// apply setting changes to /etc/squeezelite.conf
if (isset($_POST['apply']) && $_POST['apply'] == '1') {
	// update squeezelite config with current MPD device num
	$array = sdbquery('select value_player from cfg_mpd where param="device"', $dbh);
	$device = $array[0]['value_player'];
	
	// update sql table
	foreach ($_POST['config'] as $key => $value) {
		if ($key == 'AUDIODEVICE') {
			$value = $device;
		}	
		cfgdb_update('cfg_sl', $dbh, $key, $value);
	}
	
	// update conf file
	submitJob('slcfgupdate', '', 'Settings updated', ($_SESSION['slsvc'] == '1' ? 'Squeezelite restarted' : ''));
}
	
// load settings
$result = cfgdb_read('cfg_sl', $dbh);
$slconfig = array();

foreach ($result as $row) {
	$slconfig[$row['param']] = $row['value'];
}

// get device names
$dev = getDeviceNames();

// renderer name
$_sl_select['renderer_name'] = $slconfig['PLAYERNAME'];

// audio device
if ($dev[0] != "") {$_sl_select['audio_device'] .= "<option value=\"0\" " . (($slconfig['AUDIODEVICE'] == '0') ? "selected" : "") . " >$dev[0]</option>\n";}
if ($dev[1] != "") {$_sl_select['audio_device'] .= "<option value=\"1\" " . (($slconfig['AUDIODEVICE'] == '1') ? "selected" : "") . " >$dev[1]</option>\n";}

// alsa params
$_sl_select['alsa_params'] = $slconfig['ALSAPARAMS'];

// output buffers
$_sl_select['output_buffers'] = $slconfig['OUTPUTBUFFERS'];

// task priority
$_sl_select['task_priority'] = $slconfig['TASKPRIORITY'];

// audio codecs
$_sl_select['audio_codecs'] = $slconfig['CODECS'];

// other options
$_sl_select['other_options'] = $slconfig['OTHEROPTIONS'];

$section = basename(__FILE__, '.php');

$tpl = "sqe-config.html";
include('header.php'); 
waitWorker(1);
eval("echoTemplate(\"" . getTemplate("templates/$tpl") . "\");");
include('footer.php');
