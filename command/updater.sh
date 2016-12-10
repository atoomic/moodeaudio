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
# 2016-08-28 2.7 TC in-place updater
#

# $1 = rXY ex: r26

cd /var/www

wget -q http://moodeaudio.org/downloads/update-$1.zip -O update-$1.zip
unzip -q -o update-$1.zip
chmod -R 0755 update
update/install.sh
rm -rf update
rm update-$1.zip

wget -q http://moodeaudio.org/downloads/update-$1.txt -O update-$1.txt
echo "Update installed, REBOOT required"

cd ~/
