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

jQuery(document).ready(function($){ 'use strict';

	// connect mpd engine
	engineMpd();

	// hide playback controls	
	$('.playback-controls').removeClass('playback-controls-sm');
	$('.playback-controls').addClass('hidden');
	$('#playback-page-cycle').css({"display":"none"});

	// NET config: show/hide DHCP static configuration fields
	if ($('#eth0-method').length ) {
		if ($('#eth0-method').val() == 'static') {
			$('#eth0-static').show();
		}
		                        
		$('#eth0-method').change(function() {          
			if ($(this).val() == 'dhcp') {
				$('#eth0-static').hide();
			} else {
				$('#eth0-static').show();
			}                                                            
		});
	}
	if ($('#wlan0-method').length ) {
		if ($('#wlan0-method').val() == 'static') {
			$('#wlan0-static').show();
		}
		                        
		$('#wlan0-method').change(function() {          
			if ($(this).val() == 'dhcp') {
				$('#wlan0-static').hide();
			} else {
				$('#wlan0-static').show();
			}                                                            
		});
	}

	// NAS config: show/hide userid and password fields, set mount options
	if ($('#type').length) {
		if ($('#type').val() == 'cifs') {
			$('#userid-password').show();
			$('#options').val('cache=strict,ro,dir_mode=0777,file_mode=0777');
		} else {
			$('#userid-password').hide();
			$('#options').val('nfsvers=3,ro,noatime');
		}
		
		$('#type').change(function() {          
			if ($(this).val() == 'cifs') {
				$('#userid-password').show();
				$('#options').val('cache=strict,ro,dir_mode=0777,file_mode=0777');
			} else {
				$('#userid-password').hide();
				$('#options').val('nfsvers=3,ro,noatime');
			}                       
		});
	}
	
	// NAS config: show/hide advanced options
	if( $('.show-advanced-config').length ) {
		$('.show-advanced-config').click(function(e) {
			e.preventDefault();
			if ($(this).hasClass('active')) {
				$('.advanced-config').hide();
				$(this).removeClass('active');
				$(this).find('i').removeClass('icon-minus-sign').addClass('icon-plus-sign');
				$(this).find('span').html('Show advanced options');
			} else {
				$('.advanced-config').show();
				$(this).addClass('active');
				$(this).find('i').removeClass('icon-plus-sign').addClass('icon-minus-sign');
				$(this).find('span').html('Hide advanced options');
			}
		});	
	}
	
    // info show/hide toggle
    $('.info-toggle').click(function() {
		var spanId = '#' + $(this).data('cmd');
		if ($(spanId).hasClass('hide')) {
			$(spanId).removeClass('hide');
		} else {
			$(spanId).addClass('hide');
		}
    });

	// plaback history first/last page click handlers
    $('.ph-firstPage').click(function() {
        $('#container-playhistory').scrollTo(0 , 500);
    });
    $('.ph-lastPage').click(function() {
        $('#container-playhistory').scrollTo('100%', 500);
    });

	// customization settings first/last page click handlers
    $('.cs-firstPage').click(function() {
        $('#container-customize').scrollTo(0 , 500);
    });
    $('.cs-lastPage').click(function() {
        $('#container-customize').scrollTo('100%', 500);
    });

    // playlist history typedown search
    $('#ph-filter').keyup(function() {
        $.scrollTo(0 , 500);
        var filter = $(this).val(), count = 0;
        $('.playhistory li').each(function() {
            if ($(this).text().search(new RegExp(filter, 'i')) < 0) {
                $(this).hide();
            } else {
                $(this).show();
                count++;
            }
        });
        
		// change format of search results line
        var s = (count == 1) ? '' : 's';
        if (filter != '') {
            $('#ph-filter-results').html((+count) + '&nbsp;item' + s);
        } else {
            $('#ph-filter-results').html('');
        }
    });
});
