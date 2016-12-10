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
# 2016-08-28 2.7 TC volume interface for rotary encoder driver
# 2016-11-27 3.0 TC optimize sql statements
#

SQLDB=/var/www/db/player.db

# get config settings
RESULT=$(sqlite3 $SQLDB "select value from cfg_engine where id in ('32', '35', '37', '39', '40')")

# friendly names
readarray -t arr <<<"$RESULT"
VOLCURVE=${arr[0]}
VOLKNOB=${arr[1]}
VOLWARNING=${arr[2]}
AMIXNAME=${arr[3]}
MPDMIXER=${arr[4]}

# card 0 = i2s or onboard, card 1 = usb 
TMP=$(cat /proc/asound/card1/id 2>/dev/null)
if [[ $TMP = "" ]]; then CARDNUM=0; else CARDNUM=1; fi

# volume step
if [[ $1 = "up" ]]; then
	LEVEL=$(($VOLKNOB + $2))
elif [[ $1 = "dn" ]]; then
	LEVEL=$(($VOLKNOB - $2))
fi
	
# range check
if (( $LEVEL < 0 )); then
	LEVEL=0
elif (( LEVEL > VOLWARNING )); then
	LEVEL=$VOLWARNING
fi

# update knob level
$(sqlite3 $SQLDB "update cfg_engine set value=$LEVEL where id='35'")

# set volume level
if [[ $MPDMIXER = "hardware" ]]; then
	# hardware volume: update ALSA volume --> MPD volume --> MPD idle timeout --> UI updated
	if [[ $VOLCURVE = "Yes" ]]; then
		amixer -c $CARDNUM sset "$AMIXNAME" -M $LEVEL% > /dev/null
	else
		amixer -c $CARDNUM sset "$AMIXNAME" $LEVEL% > /dev/null
	fi
else
	# software volume: update MPD volume --> MPD idle timeout --> UI updated
	mpc volume $LEVEL >/dev/null
fi
