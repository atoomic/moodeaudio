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
 * 2016-08-28 2.7 TC remove rotaryenc-link from list
 *
 */
 
$(document).on('click', 'a', function(event) {
		//console.log('links.js: this.id=', this.id);
		//console.log('links.js: this.className=', this.className);
		//console.log('links.js: this.attributes=', this.attributes);
		//console.log('links.js: $(this).attr(tabindex)', $(this).attr('tabindex'));
		//return;

	    // don't modify link if matches condition below
		if (this.id == 'menu-settings' || 
			this.id == 'coverart-link' || 
			this.id == 'dlnasvc-link' || 
			this.className == 'moode-about-link1' || 
			this.className == 'moode-about-link2' ||
			this.className == 'playhistory-link' || 
			// input dropdowns on config pages
			(this.className == 'active' && $(this).attr('tabindex') == 0)) {
				
			//console.log('links.js: link not modified, match found in exclusion list');
			return;
		} 
		
	    if (!$(this).hasClass('external')) {
			//console.log('links.js: link will be modified, does not have class external');
	        event.preventDefault();
	        if (!$(event.target).attr('href')) {
       			//console.log('links.js: link modified, case 1: does not have attr href');
	            location.href = $(event.target).parent().attr('href');
	        } else {
       			//console.log('links.js: link modified, case 2: has attr href');
	            location.href = $(event.target).attr('href');
	        }
	    } else {
			//console.log('links.js: link not modified, not in exclusion list but has class external');
			// place holder   
	    }
    }
);
