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

// update mpd database
if (isset($_POST['updatempd'])) {
	submitJob('updmpddb', '', 'DB update initiated', '');
}

// update source config
if(isset($_POST['mount']) && !empty($_POST['mount'])) {
	$_POST['mount']['remotedir'] = str_replace('\\', '/', $_POST['mount']['remotedir']); // convert slashes
	// defaults
	if ($_POST['mount']['wsize'] == '') {$_POST['mount']['wsize'] = 65536;}
	if ($_POST['mount']['rsize'] == '') {$_POST['mount']['rsize'] = 61440;}
	// options
	if ($_POST['mount']['options'] == '') {
		if ($_POST['mount']['type'] == 'cifs') {
			$_POST['mount']['options'] = "cache=strict,ro,dir_mode=0777,file_mode=0777"; // TC remove noatime option, not supported on kernels >= 3.12.26+
		} else {
			$_POST['mount']['options'] = "nfsvers=3,ro,noatime";
		}
	}
	// delete or save (add, edit)
	if (isset($_POST['delete']) && $_POST['delete'] == 1) {
		$_POST['mount']['action'] = 'delete';
		submitJob('sourcecfg', $_POST, 'Mount point removed', 'DB update initiated');
	} else {
		submitJob('sourcecfg', $_POST, 'Mount point saved', 'DB update initiated');
	}
}

// also does db update after sourcecfg job completes
waitWorker(1, 'src-config');

$mounts = cfgdb_read('cfg_source',$dbh);
$tpl = "src-config.html";

foreach ($mounts as $mp) {
	if (wrk_checkStrSysfile('/proc/mounts',$mp['name']) ) {
		$icon = "<i class='icon-ok green sx'></i>";
	} else {
		$icon = "<i class='icon-remove red sx'></i>";
	}

	$_mounts .= "<p><a href=\"src-config.php?cmd=edit&id=" . $mp['id'] . "\" class='btn btn-large' style='width: 240px;'> " . $icon . " " . $mp['name'] . " (" . $mp['address'] . ") </a></p>";
}

// messages
if ($mounts === true) {
	$_mounts .= '<p class="btn btn-large" style="width: 240px;">None configured</p>';
} else if ($mounts === false) {
	$_mounts .= '<p class="btn btn-large" style="width: 240px;">Query failed</p>';
}

$section = basename(__FILE__, '.php');

include('header.php'); 

if (isset($_GET['cmd']) && !empty($_GET['cmd'])) {
	if (isset($_GET['id']) && !empty($_GET['id'])) {
		$_id = $_GET['id'];
		foreach ($mounts as $mp) {
			if ($mp['id'] == $_id) {
				$_name = $mp['name'];
				$_address = $mp['address'];
				$_remotedir = $mp['remotedir'];
				$_username = $mp['username'];
				$_password = $mp['password'];
				$_rsize = $mp['rsize'];
				$_wsize = $mp['wsize'];
				// mount type select
				$_source_select['type'] .= "<option value=\"cifs\" " . (($mp['type'] == 'cifs') ? "selected" : "") . " >SMB/CIFS</option>\n";	
				$_source_select['type'] .= "<option value=\"nfs\" " . (($mp['type'] == 'nfs') ? "selected" : "") . " >NFS</option>\n";	
				$_charset = $mp['charset'];
				$_options = $mp['options'];
				$_error = $mp['error'];
				if (empty($_error)) {
					$_hideerror = 'hide';
				}
			}
		}
		$_title = 'Edit source';
		$_action = 'edit';
	} else {
		$_title = 'Configure new source';
		$_hide = 'hide';
		$_hideerror = 'hide';
		$_action = 'add';
		$_source_select['type'] .= "<option value=\"cifs\">SMB/CIFS</option>\n";	
		$_source_select['type'] .= "<option value=\"nfs\">NFS</option>\n";	
		// testing
		$_rsize = '61440';
		$_wsize = '65536';
		$_options = 'cache=strict,ro,dir_mode=0777,file_mode=0777';
	}
	$tpl = 'nas-config.html';
} 

eval("echoTemplate(\"".getTemplate("templates/$tpl")."\");");

include('footer.php');
