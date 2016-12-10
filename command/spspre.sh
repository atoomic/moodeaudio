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
# 2016-02-27 2.5 TC shairport-sync pre-start script
# 2016-06-07 2.6 TC airplay active flag
# 2016-08-28 2.7 TC cleanup some syntax
#

SQLDB=/var/www/db/player.db

# stop playback
/usr/bin/mpc stop > /dev/null

# allow time for ui update
sleep 1

# set airplay active flag to true
$(sqlite3 $SQLDB "update cfg_engine set value='1' where param='airplayactv'")
