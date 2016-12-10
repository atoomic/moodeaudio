<!-- 
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
 * - version bump
 * - add pkgdate
 * - add contribs for Crossfeed (Boris Mikhaylov), Library load (Brendan Pike)
 * 2016-11-27 3.0 TC
 * - version bump
 * - add contrib for Advanced audio kernel (Clive Messer, Martin Sperl)
 * - add contrib for Squeezelite (Adrian Smith, Ralph Irving)
 * - add contrib for Squeezelite integration, kernel builds, low latency enhancements, UI improvements (Klaus Schulz)
 * - add "Audio" to configuration menu
 * 2016-12-05 3.1 TC
 * - version bump
 * - add moodeaudio.org and twitter links
 *
 */
-->
<!-- About -->	
<div id="about-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="about-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="about-modal-label">About Moode</h3>
	</div>
	<div class="modal-body">
		<p>
			<img src="images/player-logotype-v2-transparent-wt.png" style="height: 48px;">
			<p>Moode Audio Player is a derivative of the wonderful WebUI audio player client for MPD originally designed and coded by Andrea Coiutti and Simone De Gregori, and subsequently enhanced by efforts from the RaspyFi/Volumio projects.</p>
			<ul>
				<li>Release: 3.1 2016-12-05 <a class="moode-about-link1" href="./relnotes.txt" target="_blank">release notes</a></li>
				<li>Update: (<span id="sys-upd-pkgdate"></span>)</li>
				<li>Setup guide: <a class="moode-about-link1" href="./readme.txt" target="_blank">setup guide</a></li>
				<li>Coding:	Tim Curtis &copy; 2014 <a class="moode-about-link1" href="http://moodeaudio.org" target="_blank">moodeaudio.org</a>, <a class="moode-about-link1" href="https://twitter.com/MoodeAudio" target="_blank">twitter</a></li>
				<li>License: GPL, Warranty: NONE <a class="moode-about-link1" href="#gpl-license">(see below)</a></li>
			</ul>
		</p>
		<p>
			<h4>Platform Information</h4>			
			<ul>
				<li><img src="images/moodeos-logotype-v1-transparent.png" style="height: 20px;">ver: <span id="sys-moodeos-ver"></span></li>
				<li>Linux kernel: <span id="sys-kernel-ver"></span></li>
				<li>Architecture: <span id="sys-processor-arch"></span></li>
				<li>MPD version: <span id="sys-mpd-ver"></span></li>
				<li>Hdwr rev: <span id="sys-hardware-rev"></span></li>
			</ul>
		</p>
		<p>
			<h4>Contributions and Acknowledgements</h4>
			<p>
				The following is a list of contributors including those whose names appeared in the original code. Note this list may not be all inclusive.
			</p>
			<h6>Moode Feature Ideas and Technical Contribs</h6>
			<p> 
				Walker Boyd, Brendan Pike: MPD/PHP configs for improved Library capacity<br>
				Ralf Braun: UPnP album art and metadata parsing<br>
				Bob Daggg: Clock radio and Playback history log<br>
				Alan Finnie: Logarithmic volume control<br>
				Dr. Panagiotis Karavitis: Playback panel with integrated Playlist<br>
				Mike Thornbury, Gordon Garrity: Access Point (AP) mode<br>
			</p>
			<h6>Source code and Configs</h6>
			<p>
				Brad Daily: DOM Immediate Update<br>
				<a class="moode-about-link1" href="http://stackoverflow.com" target="_blank">http://stackoverflow.com</a><br>
				Anthony Ryan Delorie: JSON Sort Routine<br>
				<a class="moode-about-link1" href="http://stackoverflow.com" target="_blank">http://stackoverflow.com</a><br>
				Jean-Francois Dockes: author of upexplorer, a UPnP interface utility<br>
				<a class="moode-about-link1" href="http://www.lesbonscomptes.com/upmpdcli" target="_blank">http://www.lesbonscomptes.com/upmpdcli</a><br>
				Gordon Garrity: author of IQ_rot, IQ_ir device drivers<br>
				<a class="moode-about-link1" href="https://github.com/iqaudio" target="_blank">https://github.com/iqaudio</a><br>
				Andreas Goetz: Moode3 Prototype, Coverart module, Airplay metadata engine and many other code improvements<br>
				<a class="moode-about-link1" href="https://github.com/moodeaudio" target="_blank">https://github.com/moodeaudio</a><br>
				Rusty Hodge: founder of Soma FM and provider of high-res station logo links<br>
				<a class="moode-about-link1" href="http://somafm.com" target="_blank">http://somafm.com</a><br>
				Clive Messer and Martin Sperl: Advanced Audio Kernel (4.4.y-simple branch)<br>
				<span class="moode-about-link3">Experimental without any support or warranty whatsoever</span><br>
				<a class="moode-about-link1" href="https://github.com/moodeaudio/linux" target="_blank">https://github.com/moodeaudio/linux</a><br>
				Klaus Schulz: Squeezelite integration, Kernel builds, Low latency config and many UI improvements<br>
				<a class="moode-about-link1" href="http://www.diyaudio.com/forums/digital-line-level/295880-mamboberry-ls-my-new-pi-hat.html#post4811081" target="_blank">diyAudio Digital Line Level forum</a><br>
				Richard Parslow: System config settings for keyboard and layout codes<br>
				<a class="moode-about-link1" href="http://www.diyaudio.com/forums/pc-based/271811-moode-audio-player-raspberry-pi-361.html#post4757772" target="_blank">diyAudio Moode thread</a><br>
			</p>
			<h6>Core Components</h6>
			<p>
				Raspberry Pi by Eben Upton, Rob Mullins, Jack Lang,	Alan Mycroft, David Braben,	and Pete Lomas<br>
				<a class="moode-about-link2" href="https://www.raspberrypi.org/about" target="_blank">https://www.raspberrypi.org/about</a><br>
				Raspbian by Mike Thompson (mpthompson), Peter Green (plugwash) and the entire Raspberry Pi community<br>
				<a class="moode-about-link2" href="https://www.raspbian.org/RaspbianAbout" target="_blank">https://www.raspbian.org/RaspbianAbout</a><br>
				Debian Linux created by Ian Murdock in 1993<br>
				<a class="moode-about-link2" href="http://www.debian.org/" target="_blank">http://www.debian.org</a><br>
				Auto-Shuffle by Josh Kunz<br>
				<a class="moode-about-link2" href="https://github.com/Joshkunz/ashuffle" target="_blank">https://github.com/Joshkunz/ashuffle</a><br>
				Bootstrap by @mdo and @fat<br>
				<a class="moode-about-link2" href="http://twitter.github.io/bootstrap/" target="_blank">http://twitter.github.io/bootstrap</a><br>
				Bootstrap-select by caseyjhol<br>
				<a class="moode-about-link2" href="http://silviomoreto.github.io/bootstrap-select/" target="_blank">http://silviomoreto.github.io/bootstrap-select</a><br>
				Crossfeed by Boris Mikhaylov<br>
				<a class="moode-about-link2" href="http://bs2b.sourceforge.net" target="_blank">http://bs2b.sourceforge.net</a><br>
				djmount by Rémi Turboult<br>
				<a class="moode-about-link2" href="http://djmount.sourceforge.net/" target="_blank">http://djmount.sourceforge.net</a><br>
				Dnsmasq by Simon Kelly<br>
				<a class="moode-about-link2" href="http://www.thekelleys.org.uk/dnsmasq/doc.html" target="_blank">http://www.thekelleys.org.uk/dnsmasq/doc.html</a><br>
				Flat UI by Designmodo<br>
				<a class="moode-about-link2" href="http://designmodo.github.io/Flat-UI/" target="_blank">http://designmodo.github.io/Flat-UI</a><br>
				Font Awesome by Dave Gandy<br>
				<a class="moode-about-link2" href="http://fontawesome.io/" target="_blank">http://fontawesome.io</a><br>
				Hostapd by Jouni Malinen<br>
				<a class="moode-about-link2" href="http://w1.fi/hostapd/" target="_blank">http://http://w1.fi/hostapd/</a><br>
				jQuery Countdown by Keith Wood<br>
				<a class="moode-about-link2" href="http://keith-wood.name/countdown.html" target="_blank">http://keith-wood.name/countdown.html</a><br>
				jQuery Knob by Anthony Terrien<br>
				<a class="moode-about-link2" href="https://github.com/aterrien/jQuery-Knob" target="_blank">https://github.com/aterrien/jQuery-Knob</a><br>
				jQuery scrollTo by Ariel Flesler<br>
				<a class="moode-about-link2" href="http://flesler.blogspot.it/2007/10/jqueryscrollto.html" target="_blank">http://flesler.blogspot.it/2007/10/jqueryscrollto.html</a><br>
				Lato-Fonts by Łukasz Dziedzic<br>
				<a class="moode-about-link2" href="http://www.latofonts.com/lato-free-fonts/" target="_blank">http://www.latofonts.com/lato-free-fonts</a><br>
				MiniDLNA by Justin Maggard<br>
				<a class="moode-about-link2" href="http://minidlna.sourceforge.net/" target="_blank">http://minidlna.sourceforge.net</a><br>
				MPD by Max Kellermann and Warren Dukes<br>
				<a class="moode-about-link2" href="http://www.musicpd.org/" target="_blank">http://www.musicpd.org</a><br>
				MPD Audio Scrobbler by Henrik Friedrichsen<br>
				<a class="moode-about-link2" href="https://github.com/hrkfdn/mpdas" target="_blank">https://github.com/hrkfdn/mpdas</a><br>
				PHP v5 by the PHP Team<br>
				<a class="moode-about-link2" href="http://php.net" target="_blank">http://php.net</a><br>
				Shairport-sync by Mike Brady<br>
				<a class="moode-about-link2" href="https://github.com/mikebrady/shairport-sync" target="_blank">https://github.com/mikebrady/shairport-sync</a><br>
				SQLite v3 by the SQLite Team<br>
				<a class="moode-about-link2" href="http://www.sqlite.org" target="_blank">http://www.sqlite.org</a><br>
				Squeezelite by Adrian Smith and Ralph Irving<br>
				<a class="moode-about-link2" href="https://github.com/ralph-irving/squeezelite" target="_blank">https://github.com/ralph-irving/squeezelite</a><br>
				Udisks-glue by Fernando Tarlá Cardoso Lemos<br>
				<a class="moode-about-link2" href="https://github.com/fernandotcl/udisks-glue" target="_blank">https://github.com/fernandotcl/udisks-glue</a><br>
				Upmpdcli by Jean-Francois Dockes<br>
				<a class="moode-about-link2" href="http://www.lesbonscomptes.com/upmpdcli/" target="_blank">http://www.lesbonscomptes.com/upmpdcli/</a><br>
				WiringPi GPIO access library by Gordon Henderson<br>
				<a class="moode-about-link1" href="http://wiringpi.com" target="_blank">http://wiringpi.com</a><br>
			</p>		
			<div id="original-code">
				<h6>Original Code</h6>
				<p> 
					Andrea Coiutti: WebUI design, HTML/CSS/JS coding<br>
					Simone De Gregori: PHP/MPD/JS coding and OS optimizations<br>
					<a class="moode-about-link1" href="http://runeaudio.com" target="_blank">http://runeaudio.com</a><br>
					Michelangelo Guarise: RaspyFi/Volumio enhancements, OS image build<br>
					- One and a half year of work more than Raspyfi's WebUI made by ACX and Orion<br>
					- Work has been performed by me, Jotak and other Volumio community members<br>
					<a class="moode-about-link1" href="http://volumio.org" target="_blank">http://volumio.org</a><br>
					Joel Takvorian (jotak): Library Loader and Panel v1<br>
					<a class="moode-about-link1" href="http://volumio.org/forum/web-enhancements-t1236.html" target="_blank">volumio forum post</a><br>
					Jan Sandred (jansandred): Radio Station PLS Files (original set)<br>
					<a class="moode-about-link1" href="http://volumio.org/forum/internet-radio-stations-volumio-t641.html" target="_blank">volumio forum post</a><br>
				</p>
			</div>
		</p>
		<div id="gpl-license">
			<h4>License Information</h4>
			<p>
				This Program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation either version 3, or (at your option) any later version. This Program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this software; refer to the file named LICENSE. If not, refer to the following online resource for the license: <a class="moode-about-link1" href="http://www.gnu.org/licenses/" target="_blank">http://www.gnu.org/licenses</a>
			</p>
		</div>
		<div id="warranty-info">
			<h4>Waranty Information</h4>
			<p>
				This software is provided for free by the copyright holders and contributors and comes with no expressed or implied warranties or any other guarantees.
			</p>
		</div>	
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	</div>
</div>

<!-- Restart -->	
<div id="restart-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="restart-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="restart-modal-label">Restart system</h3>
	</div>
	<div class="modal-body">
		<button id="syscmd-poweroff" data-dismiss="modal" class="btn btn-primary btn-large btn-block"><i class="icon-power-off sx"></i> Shutdown</button>
		<button id="syscmd-reboot" data-dismiss="modal" class="btn btn-primary btn-large btn-block"><i class="icon-refresh sx"></i> Reboot</button>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
	</div>
</div>

<!-- Audio information -->	
<div id="audioinfo-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="audioinfo-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="audioinfo-modal-label">Audio information</h3>
	</div>
	<div class="modal-body">
	</div>
	<!-- There is a custom footer for this modal
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	</div>
	-->
</div>

<!-- Delete confirmation -->	
<div id="deletesavedpl-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="deletesavedpl-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="deletesavedpl-modal-label">Delete saved playlist</h3>
	</div>
	<div class="modal-body">
		<h4 id='savedpl-path'></h4>
	</div>
	<div class="modal-footer">
		<button class="btn btn-del-savedpl btn-primary" data-dismiss="modal">Delete Playlist</button>
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
	</div>
</div>

<div id="deletestation-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="deletestation-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="deletestation-modal-label">Delete radio station</h3>
	</div>
	<div class="modal-body">
		<h4 id='station-path'></h4>
	</div>
	<div class="modal-footer">
		<button class="btn btn-del-radiostn btn-primary" data-dismiss="modal">Delete Station</button>
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
	</div>
</div>

<!-- Radio station maintenance -->	
<div id="addstation-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="addstation-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="addstation-modal-label">Create radio station</h3>
	</div>
	<div class="modal-body">
		<form class="form-horizontal" data-validate="parsley" action="" method="">
	    	<fieldset>
				<div class="control-group">
	                <label class="control-label" for="add-station-name">Station name</label>
	                <div class="controls">
	                    <input id="add-station-name" class="input-xlarge" type="text" name="add-station_name" size="200" value="">
	                </div>
	                <label class="control-label" for="add-station-url">Station URL</label>
	                <div class="controls">
	                    <input id="add-station-url" class="input-xlarge" type="text" name="add-station_url" size="200" value="">
	                </div>
	            </div>
	    	</fieldset>
		</form>
	</div>
	<div class="modal-footer">
		<button class="btn btn-add-radiostn btn-primary" data-dismiss="modal">Add Station</button>
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
	</div>
</div>

<div id="editstation-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="editstation-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="editstation-modal-label">Edit radio station</h3>
	</div>
	<div class="modal-body">
		<form class="form-horizontal" data-validate="parsley" action="" method="">
	    	<fieldset>
				<div class="control-group">
	                <label class="control-label" for="edit-station-name">Station name</label>
	                <div class="controls">
	                    <input id="edit-station-name" class="input-xlarge" type="text" name="edit_station_name" size="200" value="">
	                </div>
	                <label class="control-label" for="edit-station-url">Station URL</label>
	                <div class="controls">
	                    <input id="edit-station-url" class="input-xlarge" type="text" name="edit_station_url" size="200" value="">
	                </div>
	            </div>
	    	</fieldset>
		</form>
	</div>
	<div class="modal-footer">
		<button class="btn btn-update-radiostn btn-primary" data-dismiss="modal">Update Station</button>
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
	</div>
</div>

<!-- Playlist maintenance -->	
<div id="deleteplitems-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteplitems-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="deleteplitems-modal-label">Remove playlist items</h3>
	</div>
	<div class="modal-body">
		<form class="form-horizontal" data-validate="parsley" action="" method="">
	    	<fieldset>
				<div class="control-group">
	                <label class="control-label" for="delete-plitem-begpos">Beginning item</label>
	                <div class="controls">
	                    <input id="delete-plitem-begpos" class="input-small" style="height: 20px;" type="number" min="1" max="" name="delete_plitem_begpos" value="">
						<button id="btn-delete-setpos-top" class="btn btn-mini btn-default"><i class="icon-double-angle-up"></i></button>
	                </div>
	                <label class="control-label" for="delete-plitem-endpos">Ending item</label>
	                <div class="controls">
	                    <input id="delete-plitem-endpos" class="input-small" style="height: 20px;" type="number"  min="1" max="" name="delete_plitem_endpos" value="">
						<button id="btn-delete-setpos-bot" class="btn btn-mini btn-default"><i class="icon-double-angle-down"></i></button>
	                </div>
	            </div>
	    	</fieldset>
		</form>
	</div>
	<div class="modal-footer">
		<button class="btn btn-delete-plitem btn-primary" data-dismiss="modal">Remove items</button>
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
	</div>
</div>

<div id="moveplitems-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="moveplitems-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="moveplitems-modal-label">Move playlist items</h3>
	</div>
	<div class="modal-body">
		<form class="form-horizontal" data-validate="parsley" action="" method="">
	    	<fieldset>
				<div class="control-group">
	                <label class="control-label" for="move-plitem-begpos">Beginning item</label>
	                <div class="controls">
	                    <input id="move-plitem-begpos" class="input-small" style="height: 20px;" type="number"  min="1" max="" name="move_plitem_begpos" value="">
						<button id="btn-move-setpos-top" class="btn btn-mini btn-default"><i class="icon-double-angle-up"></i></button>
	                </div>
	                <label class="control-label" for="move-plitem-endpos">Ending item</label>
	                <div class="controls">
	                    <input id="move-plitem-endpos" class="input-small" style="height: 20px;" type="number"  min="1"  max="" name="move_plitem_endpos" value="">
						<button id="btn-move-setpos-bot" class="btn btn-mini btn-default"><i class="icon-double-angle-down"></i></button>
	                </div>
	                <label class="control-label" for="move-plitem-newpos">New position</label>
	                <div class="controls">
	                    <input id="move-plitem-newpos" class="input-small" style="height: 20px;" type="number"  min="1"  max="" name="move_plitem_newpos" value="">
						<button id="btn-move-setnewpos-top" class="btn btn-mini btn-default"><i class="icon-double-angle-up"></i></button>
						<button id="btn-move-setnewpos-bot" class="btn btn-mini btn-default"><i class="icon-double-angle-down"></i></button>
	                </div>
	            </div>
	    	</fieldset>
		</form>
	</div>
	<div class="modal-footer">
		<button class="btn btn-move-plitem btn-primary" data-dismiss="modal">Move Items</button>
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
	</div>
</div>

<!-- Clock radio -->	
<div id="clockradio-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="clockradio-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="clockradio-modal-label">Clock radio settings</h3>
	</div>
	<div class="modal-body">
		<form class="form-horizontal" data-validate="parsley" action="" method="">
	    	<fieldset>
				<div class="control-group">
	                <label class="control-label" for="clockradio-enabled">Enabled</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select bootstrap-select-mini"> <!-- handler in playerlib.js -->
							<button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="clockradio-enabled" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open">
								<ul class="dropdown-menu custom-select inner" role="menu">
									<li><a href="#notarget" data-cmd="clockradio-enabled-yn"><span class="text">Yes</span></a></li>
									<li><a href="#notarget" data-cmd="clockradio-enabled-yn"><span class="text">No</span></a></li>
								</ul>
							</div>
						</div>
	                </div>
	                
	                <label class="control-label" for="clockradio-playname">Play</label>
	                <div class="controls">
	                    <input id="clockradio-playname" class="input-xlarge" type="text" name="clockradio_playname" value="" readonly>
	                </div>
	                
	                <label class="control-label" for="clockradio-starttime-hh">Start time</label>
	                <div class="controls">
	                    <input id="clockradio-starttime-hh" class="input-mini" style="height: 20px;" type="number" maxlength="2" min="1" max="12" name="clockradio_starttime-hh" value="">
	                    <span>:</span>
	                    <input id="clockradio-starttime-mm" class="input-mini" style="height: 20px;" type="number" maxlength="2" min="0" max="59" name="clockradio_starttime-mm" value="">
						
						<div class="btn-group bootstrap-select bootstrap-select-mini"> <!-- handler in playerlib.js -->
							<button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="clockradio-starttime-ampm" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open">
								<ul class="dropdown-menu custom-select inner" role="menu">
									<li><a href="#notarget" data-cmd="clockradio-starttime-ampm"><span class="text">AM</span></a></li>
									<li><a href="#notarget" data-cmd="clockradio-starttime-ampm"><span class="text">PM</span></a></li>
								</ul>
							</div>
						</div>
	                </div>
	                
	                <label class="control-label" for="clockradio-stoptime-hh">Stop time</label>
	                <div class="controls">
	                    <input id="clockradio-stoptime-hh" class="input-mini" style="height: 20px;" type="number" maxlength="2" min="1" max="12" name="clockradio_stoptime-hh" value="">
	                    <span>:</span>
	                    <input id="clockradio-stoptime-mm" class="input-mini" style="height: 20px;" type="number" maxlength="2" min="0" max="59" name="clockradio_stoptime-mm" value="">
						
						<div class="btn-group bootstrap-select bootstrap-select-mini"> <!-- handler in playerlib.js -->
							<button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="clockradio-stoptime-ampm" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open">
								<ul class="dropdown-menu custom-select inner" role="menu">
									<li><a href="#notarget" data-cmd="clockradio-stoptime-ampm"><span class="text">AM</span></a></li>
									<li><a href="#notarget" data-cmd="clockradio-stoptime-ampm"><span class="text">PM</span></a></li>
								</ul>
							</div>
						</div>
	                </div>
	                
	                <label class="control-label" for="clockradio-volume">Volume</label>
	                <div class="controls">
	                    <input id="clockradio-volume" class="input-mini" style="height: 20px;" type="number" min="1" max="" name="clockradio_volume" value="">
						<span id="clockradio-volume-aftertext" class="control-aftertext"></span> <!-- text set in player-scripts.js -->
	                </div>
	                
	                <label class="control-label" for="clockradio-shutdown">Shutdown</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select bootstrap-select-mini"> <!-- handler in playerlib.js -->
							<button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="clockradio-shutdown" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open">
								<ul class="dropdown-menu custom-select inner" role="menu">
									<li><a href="#notarget" data-cmd="clockradio-shutdown-yn"><span class="text">Yes</span></a></li>
									<li><a href="#notarget" data-cmd="clockradio-shutdown-yn"><span class="text">No</span></a></li>
								</ul>
							</div>
						</div>
						<span class="control-aftertext">after stop</span>
	                </div>
	            </div>
	    	</fieldset>
		</form>
		<div class="modal-action-btns hide">
			<button class="btn btn-clockradio-update btn-primary" data-dismiss="modal">Update Settings</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-clockradio-update btn-primary" data-dismiss="modal">Update Settings</button>
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
	</div>
</div>

<!-- Customize -->	
<div id="customize-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="customize-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="customize-modal-label">Customization settings</h3>
	</div>
	<div class="modal-body" id="container-customize">
		<form class="form-horizontal" data-validate="parsley" action="" method="">
			<h4>General settings</h4>
	    	<fieldset>
				<div class="control-group">
	                <label class="control-label" for="volume-warning-limit">Volume warning limit</label>
	                <div class="controls">
	                    <input id="volume-warning-limit" class="input-mini" style="height: 20px;" type="number" maxlength="3" min="1" max="100" name="volume_warning_limit" value="">
						<span id="volume-warning-limit-aftertext" class="control-aftertext2"></span> <!-- text set in player-scripts.js -->
						<a class="info-toggle" data-cmd="info-volume-warning-limit" href="#notarget"><i class="icon-info-sign"></i></a>
						<span id="info-volume-warning-limit" class="help-block hide">
	                    	When the Knob volume exceeds the warning limit, a popup<br>
							appears and volume level remains unchanged. Setting the<br>
							limit to 100 disables the warning popup.<br>
							NOTE: the limit only applies to Knob changes and has no<br>
							effect on volume changes made by other applications for<br>
							example Airplay receiver, UPnP renderer and Squeezelite<br>
							renderer. These applications manage volume separately<br>
							from Moode Knob and MPD.
	                    </span>
	                </div>
	                
   	                <label class="control-label" for="search-autofocus-enabled">Search auto-focus</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select bootstrap-select-mini"> <!-- handler in playerlib.js -->
							<button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="search-autofocus-enabled" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open">
								<ul class="dropdown-menu custom-select inner" role="menu">
									<li><a href="#notarget" data-cmd="search-autofocus-enabled-yn"><span class="text">Yes</span></a></li>
									<li><a href="#notarget" data-cmd="search-autofocus-enabled-yn"><span class="text">No</span></a></li>
								</ul>
							</div>
						</div>
						<a class="info-toggle" data-cmd="info-search-audofocus" href="#notarget"><i class="icon-info-sign"></i></a>
						<span id="info-search-audofocus" class="help-block hide">
	                    	Controls whether search fields automatically receive focus when the toolbar shows.<br>
	                    	- On Smartphone/Tablet, autofocus will cause the popup keyboard to appear.
	                    </span>
	                </div>

   	                <label class="control-label" for="theme-color">Theme color</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select bootstrap-select" style="width: 110px;"> <!-- handler in playerlib.js -->
							<button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="theme-color" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open"> <!-- list generated in playerlib.js -->
								<ul id="theme-color-list" class="dropdown-menu custom-select inner" role="menu"></ul>
							</div>
						</div>
	                </div>
	                
   	                <label class="control-label" for="play-history-enabled">Playback history</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select bootstrap-select-mini"> <!-- handler in playerlib.js -->
							<button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="play-history-enabled" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open">
								<ul class="dropdown-menu custom-select inner" role="menu">
									<li><a href="#notarget" data-cmd="play-history-enabled-yn"><span class="text">Yes</span></a></li>
									<li><a href="#notarget" data-cmd="play-history-enabled-yn"><span class="text">No</span></a></li>
								</ul>
							</div>
						</div>
						<a class="info-toggle" data-cmd="info-play-history" href="#notarget"><i class="icon-info-sign"></i></a>
						<span id="info-play-history" class="help-block hide">
	                    	Select Yes to log each song played to the playback history log.<br>
	                    	- Songs in the log can be clicked to launch a Google search.<br>
	                    	- The log can be cleared from the System configuration page.
	                    </span>
	                </div>					

   	                <label class="control-label" for="playlist-display">Display extra metadata</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select bootstrap-select-mini"> <!-- handler in playerlib.js -->
							<button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="extratag-display" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open">
								<ul class="dropdown-menu custom-select inner" role="menu">
									<li><a href="#notarget" data-cmd="extratag-display-yn"><span class="text">Yes</span></a></li>
									<li><a href="#notarget" data-cmd="extratag-display-yn"><span class="text">No</span></a></li>
								</ul>
							</div>
						</div>
						<a class="info-toggle" data-cmd="info-extratag-display" href="#notarget"><i class="icon-info-sign"></i></a>
						<span id="info-extratag-display" class="help-block hide">
	                    	Select Yes to display additional metadata<br>
	                    	- menu, refresh after changing this setting
	                    </span>
	                </div>
					
   	                <label class="control-label" for="playlist-display">Resume after Airplay</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select bootstrap-select-mini"> <!-- handler in playerlib.js -->
							<button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="resume-aftersps" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open">
								<ul class="dropdown-menu custom-select inner" role="menu">
									<li><a href="#notarget" data-cmd="resume-aftersps-yn"><span class="text">Yes</span></a></li>
									<li><a href="#notarget" data-cmd="resume-aftersps-yn"><span class="text">No</span></a></li>
								</ul>
							</div>
						</div>
						<a class="info-toggle" data-cmd="info-resume-aftersps" href="#notarget"><i class="icon-info-sign"></i></a>
						<span id="info-resume-aftersps" class="help-block hide">
	                    	Select Yes to resume Moode playback after Airplay session ends<br>
	                    </span>
	                </div>
					
	            </div>
	    	</fieldset>

			<h4>Hardware volume control</h4>
	    	<fieldset>
				<div class="control-group">
   	                <label class="control-label" for="logarithmic-curve-enabled">Logarithmic curve</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select bootstrap-select-mini"> <!-- handler in playerlib.js -->
							<button id="volcurve" type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="logarithmic-curve-enabled" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open">
								<ul class="dropdown-menu custom-select inner" role="menu">
									<li><a href="#notarget" data-cmd="logarithmic-curve-enabled-yn"><span class="text">Yes</span></a></li>
									<li><a href="#notarget" data-cmd="logarithmic-curve-enabled-yn"><span class="text">No</span></a></li>
								</ul>
							</div>
						</div>
						<a class="info-toggle" data-cmd="info-logarithmic-curve" href="#notarget"><i class="icon-info-sign"></i></a>
						<span id="info-logarithmic-curve" class="help-block hide">
	                    	Maps volume knob 0-100 range to the audio device hardware volume range using a logarithmic curve.
	                    </span>
	                </div>
	                
	                <label class="control-label" for="volume-curve-factor">Curve slope</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select bootstrap-select" style="width: 110px;"> <!-- handler in playerlib.js -->
							<button id="volcurvefac" type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="volume-curve-factor" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open"> <!-- list generated in playerlib.js -->
								<ul id="volume-curve-list" class="dropdown-menu custom-select inner" role="menu"></ul>
							</div>
						</div>
						<a class="info-toggle" data-cmd="info-curve-factor" href="#notarget"><i class="icon-info-sign"></i></a>
						<span id="info-curve-factor" class="help-block hide">
	                    	Adjusts the slope of the volume curve.<br>
	                    	- Less slope causes lower volume output in the 0 - 50 range.<br>
	                    	- More slope causes higher volume output in the 0 - 50 range.
	                    </span>
	                </div>

	                <label class="control-label" for="volume-max-percent">Maximum volume (%)</label>
	                <div class="controls">
	                    <input id="volume-max-percent" class="input-mini" style="height: 20px;" type="number" maxlength="3" min="1" max="100" name="volume_max_percent" value="">
						<a class="info-toggle" data-cmd="info-volume-max" href="#notarget"><i class="icon-info-sign"></i></a>
						<span id="info-volume-max" class="help-block hide">
	                    	Sets the maximum volume level output (100% is default).
	                    </span>
	                </div>

	            </div>
	    	</fieldset>

			<h4>Chip options (PCM5242, PCM5142, PCM512x, TAS5756)</h4>
	    	<fieldset>
				<div class="control-group">
   	                <label class="control-label" for="pcm5122-filter-name">Digital interpolation filter</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select" style="width: 265px;"> <!-- handler in playerlib.js -->
							<button id="pcm5122-filtername" type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="pcm5122-filter-name" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open"> <!-- list generated in playerlib.js -->
								<ul id="pcm5122-filter-list" class="dropdown-menu custom-select inner" role="menu"></ul>
							</div>
						</div>
						<a class="info-toggle" data-cmd="info-filter-name" href="#notarget"><i class="icon-info-sign"></i></a>
						<span id="info-filter-name" class="help-block hide">
	                    	Filter is applied to PCM samples at oversampling stage<br>
							just prior to sigma-delta modulator. Playback is automatically<br>
							restarted to make filter change effective.<br>
							NOTE: Filter is bypassed if sample rate is 384 kHz.
	                    </span>
	                </div>
					
	                <label class="control-label" for="pcm5122-analog-vol">Analog volume</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select bootstrap-select-mini" style="width: 74px;"> <!-- handler in playerlib.js -->
							<button id="pcm5122-analogvol" type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="pcm5122-analog-vol" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open"> <!-- list generated in playerlib.js -->
								<ul id="pcm5122-analog-vol-list" class="dropdown-menu custom-select inner" role="menu"></ul>
							</div>
						</div>
						<a class="info-toggle" data-cmd="info-analog-vol" href="#notarget"><i class="icon-info-sign"></i></a>
						<span id="info-analog-vol" class="help-block hide">
							 Analog volume gain can be set to 0 dB or -6 dB.
	                    </span>
	                </div>

	                <label class="control-label" for="pcm5122-analog-pbb">Analog playback boost</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select bootstrap-select-mini" style="width: 74px;"> <!-- handler in playerlib.js -->
							<button id="pcm5122-analogpbb" type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="pcm5122-analog-pbb" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open"> <!-- list generated in playerlib.js -->
								<ul id="pcm5122-analog-pbb-list" class="dropdown-menu custom-select inner" role="menu"></ul>
							</div>
						</div>
						<a class="info-toggle" data-cmd="info-analog-pbb" href="#notarget"><i class="icon-info-sign"></i></a>
						<span id="info-analog-pbb" class="help-block hide">
							 Analog playback boost gain can be set to .8 dB or 0 dB.
	                    </span>
	                </div>
	            </div>
	    	</fieldset>
	    	
			<h4>Audio device description</h4>
	    	<fieldset>
				<div class="control-group">
   	                <label class="control-label" for="audio-device-name">Device</label>
	                <div class="controls">
   						<div class="btn-group bootstrap-select" style="width: 265px;"> <!-- handler in playerlib.js -->
							<button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
								<div id="audio-device-name" class="filter-option pull-left">
									<span></span> <!-- selection from dropdown gets placed here -->
								</div>&nbsp;
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu open"> <!-- list generated in playerlib.js -->
								<ul id="audio-device-list" class="dropdown-menu custom-select inner" role="menu"></ul>
							</div>
						</div>
						<a class="info-toggle" data-cmd="info-device-name" href="#notarget"><i class="icon-info-sign"></i></a>
						<span id="info-device-name" class="help-block hide">
	                    	Select a device to have its description show on Audio Info.<br>
							I2S devices are automatically populated from System config.<br>
							If device is not listed select "USB audio device".
	                    </span>
	                </div>
					
	                <label class="control-label" for="audio-device-dac">Chip</label>
	                <div class="controls">
	                    <input id="audio-device-dac" class="input-xlarge" type="text" name="audio_device_dac" value="" readonly>
	                </div>
	                <label class="control-label" for="audio-device-arch">Architecture</label>
	                <div class="controls">
	                    <input id="audio-device-arch" class="input-xlarge" type="text" name="audio_device_arch" value="" readonly>
	                </div>
	                <label class="control-label" for="audio-device-iface">Interface</label>
	                <div class="controls">
	                    <input id="audio-device-iface" class="input-xlarge" type="text" name="audio_device_iface" value="" readonly>
	                </div>
	            </div>
	    	</fieldset>
		</form>
	</div>

	<div class="modal-footer">
		<button class="btn cs-lastPage" style="float: right;"><i class="icon-double-angle-down"></i></button>
		<button class="btn cs-firstPage" style="float: right;"><i class="icon-double-angle-up"></i></button>

		<button class="btn btn-customize-update btn-primary" data-dismiss="modal">Update</button>
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
	</div>
</div>

<!-- Playback history -->	
<div id="playhistory-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="playhistory-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="playhistory-modal-label">Playback history</h3>
	</div>
	<div class="playhistory-search">
		<form id="ph-search" method="post" onSubmit="return false;">
			<div class="input-append" style="margin-bottom: 0;">
				<input id="ph-filter" type="text" value="" placeholder="search" data-placement="bottom" data-toggle="tooltip">
				<span id="ph-filter-results"></span>
				<button class="btn ph-firstPage"><i class="icon-double-angle-up"></i></button>
				<button class="btn ph-lastPage"><i class="icon-double-angle-down"></i></button>
			</div>
		</form>
	</div>
	<div class="modal-body" id="container-playhistory">
		<div id="playhistory">
			<ol class="playhistory"></ol>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	</div>
</div>


<!-- Volume warning -->	
<div id="volumewarning-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="volumewarning-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="volumewarning-modal-label">Volume warning</h3>
	</div>
	<div class="modal-body">
		<h4 id="volume-warning-text"></h4>
		<div class="context-menu">
			<a href="#notarget" data-cmd="customize" class="btn btn-primary btn-large btn-block" data-dismiss="modal">Change Warning Limit</a>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	</div>
</div>

<!-- Configuration menu -->	
<div id="configure-modal" class="modal modal-sm hide fade" tabindex="-1" role="dialog" aria-labelledby="configure-modal-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="configure-modal-label">Configuration settings</h3>
	</div>
	<div class="modal-body">
		<h4>Select one of the following configuration pages:</h4>
		<div style="margin-top: 20px; margin-left: 20px;">
			<div class="moode-config-settings-header"><a class="moode-config-settings-link" href="src-config.php"><i class="icon-folder-open sx"></i>Sources</a></div>
			<span class="help-block">
				Define the location of music files
            </span>
			<div class="moode-config-settings-header"><a class="moode-config-settings-link" href="mpd-config.php"><i class="icon-forward sx"></i>&nbsp;MPD</a></div>
			<span class="help-block">
				Music Player Daemon settings
            </span>
			<div class="moode-config-settings-header"><a class="moode-config-settings-link" href="snd-config.php"><i class="icon-volume-up sx"></i>&nbsp;Audio</a></div>
			<span class="help-block">
				Audio and renderer settings
            </span>
			<div class="moode-config-settings-header"><a class="moode-config-settings-link" href="net-config.php"><i class="icon-sitemap sx"></i>Network</a></div>
			<span class="help-block">
				LAN, WiFi and AP mode settings
            </span>
			<div class="moode-config-settings-header"><a class="moode-config-settings-link" href="sys-config.php"><i class="icon-wrench sx"></i>System</a></div>
			<span class="help-block">
				System modifications, services and maintenence
            </span>
		</div>
	</div>

	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	</div>
</div>

<!-- Reconnect, reboot and poweroff screens -->
<div id="reconnect" class="hide">
	<div id="rebootbg"></div>
	<div id="smartreboot">
		<a href="javascript:location.reload(true); void 0" class="btn btn-primary btn-large">reconnect</a>
	</div>
</div>

<div id="reboot" class="hide">
	<div id="rebootbg"></div>
	<div id="smartreboot">
		<a href="javascript:location.reload(true); void 0" class="btn btn-primary btn-large">reconnect</a>
		System rebooting
		<div id="bootready"></div>			
	</div>
</div>

<div id="poweroff" class="hide">
	<div id="poweroffbg"></div>
	<div id="smartpoweroff">
		<a href="javascript:location.reload(true); void 0" class="btn btn-primary btn-large">reconnect</a>
		System has been powered off
	</div>
</div>

<!-- JS scripts -->
<script src="js/jquery-1.8.2.min.js"></script>
<script src="js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-select.min.js"></script>
<script src="js/jquery.countdown.js"></script>
<script src="js/jquery.countdown-it.js"></script>
<script src="js/jquery.scrollTo.min.js"></script>
<!-- Moode JS -->
<script src="js/notify.js"></script>
<script src="js/playerlib.js"></script>
<script src="js/links.js"></script>

<!-- Panels get different scripts than the config pages -->
<?php if ($section == 'index') { ?>
	<script src="js/jquery.knob.js"></script>
	<script src="js/bootstrap-contextmenu.js"></script>
	<script src="js/jquery.pnotify.min.js"></script>
	<!-- Moode JS -->
	<script src="js/scripts-panels.js"></script>
<?php } else { ?>
	<script src="js/custom_checkbox_and_radio.js"></script>
	<script src="js/custom_radio.js"></script>
	<script src="js/jquery.tagsinput.js"></script>
	<script src="js/jquery.placeholder.js"></script>
	<script src="js/parsley.min.js"></script>
	<script src="js/i18n/_messages.en.js" type="text/javascript"></script>
	<script src="js/application.js"></script>
	<script src="js/jquery.pnotify.min.js"></script>
	<script src="js/bootstrap-fileupload.js"></script>
	<!-- Moode JS -->
	<script src="js/scripts-configs.js"></script>
<?php } ?>

<!-- Write backend response on UI Notify popup -->
<?php
if (isset($_SESSION['notify']) && $_SESSION['notify'] != '') {
	ui_notify($_SESSION['notify']);
	session_start();
	$_SESSION['notify'] = '';
	session_write_close();
}
?>

</body>
</html>
