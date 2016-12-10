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
# 2016-02-27 2.5 TC lcd updater, $1 is path to Python script
#

if [[ -n "$1" ]]; then
	eval "$1"

	while inotifywait -e close_write /var/www/currentsong.txt; do
		eval "$1"	
	done > /dev/null 2>&1 &
else
	echo "lcdup, missing arg <path to Python script>"
fi