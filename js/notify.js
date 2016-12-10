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
 * 2015-08-30 2.4 AG (Andreas Goetz) rewrite
 *
 */

function notify(cmd, msg) {
    msg = msg || ''; // msg optional

    var map = {
        add: 'Added to playlist',
        clrplay: 'Added after clearing playlist',
        update: 'Update path: ',
        remove: 'Removed from playlist',
        move: 'Playlist items moved',
        savepl: 'Playlist saved',
        needplname: 'Enter a name',
        delsavedpl: 'Playlist deleted',
        delstation: 'Radio station deleted',
        addstation: 'Radio station added',
        updstation: 'Radio station updated',
        updclockradio: 'Clock radio updated',
        updcustomize: 'Settings updated',
        themechange: 'Theme color changed',
        usbaudioready: 'USB audio ready'
    };

    if (typeof map[cmd] === undefined) {
        console.error('[notify] Unknown cmd ' + cmd);
    }

    var icon = (cmd == 'needplname') ? 'icon-info-sign' : 'icon-ok';
    $.pnotify({
        title: map[cmd],
        text: msg,
        icon: icon,
        delay: 2000,
        opacity: 0.9,
        history: false
    });
}
