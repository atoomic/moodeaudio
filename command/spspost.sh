#!/bin/bash
#
# This Program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3, or (at your option)
# any later version.
#
# This Program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moode; see the file COPYING.  If not, see
# <http://www.gnu.org/licenses/>.
#
# Moode Audio Player (C) 2014 Tim Curtis
# http://moodeaudio.org
#
# 2016-02-27 2.5 TC shairport-sync post-finish script
# 2016-06-07 2.6 TC airplay active flag
# 2016-08-28 2.7 TC cleanup some syntax
#

SQLDB=/var/www/db/player.db

# set airplay active flag to false
$(sqlite3 $SQLDB "update cfg_engine set value='0' where param='airplayactv'")
truncate /var/www/spscache.json --size 0

# get settings
RESULT=$(sqlite3 $SQLDB "select value from cfg_engine where param='volknob' or param='alsavolume' or param='amixname' or param='mpdmixer' or param='rsmaftersps'")

# friendly names
readarray -t arr <<<"$RESULT"
VOLKNOB=${arr[0]}
ALSAVOLUME=${arr[1]}
AMIXNAME=${arr[2]}
MPDMIXER=${arr[3]}
RSMAFTERSPS=${arr[4]}

# restore 0dB (100%) hardware volume when mpd configured as below
if [[ $MPDMIXER == "software" || $MPDMIXER == "disabled" ]]; then
	if [[ $ALSAVOLUME != "none" ]]; then
		/var/www/command/util.sh set-alsavol "$AMIXNAME" 100
	fi
fi

# restore volume
/var/www/vol.sh $VOLKNOB

# resume playback if indicated
if [[ $RSMAFTERSPS == "Yes" ]]; then
	/usr/bin/mpc play > /dev/null
fi
