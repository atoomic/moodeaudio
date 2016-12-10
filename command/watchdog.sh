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
# 2016-02-27 2.5 TC watchdog monitor
# 2016-06-07 2.6 TC moodeOS 1.0
# 2016-08-28 2.7 TC add mpd
#

FPMLIMIT=18
FPMCNT=$(pgrep -c -f "php-fpm: pool display")
MPDACTIVE=$(pgrep -c -x mpd)

while true; do
	# PHP
	if (( FPMCNT > FPMLIMIT )); then
		TIMESTAMP=$(date +'%Y%m%d %H%M%S')
		LOGMSG=" watchdog: PHP restarted (fpm child limit "$FPMLIMIT" exceeded)"
		echo $TIMESTAMP$LOGMSG >> /var/log/moode.log
		systemctl restart php5-fpm
	fi

	# MPD
	if [[ $MPDACTIVE = 0 ]]; then
		TIMESTAMP=$(date +'%Y%m%d %H%M%S')
		LOGMSG=" watchdog: MPD restarted (check syslog for SEGV)"
		echo $TIMESTAMP$LOGMSG >> /var/log/moode.log
		systemctl start mpd
	fi
		
	sleep 6
	FPMCNT=$(pgrep -c -f "php-fpm: pool display")
	MPDACTIVE=$(pgrep -c -x mpd)
done > /dev/null 2>&1 &
