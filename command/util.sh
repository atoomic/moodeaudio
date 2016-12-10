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
# 2016-02-27 2.5 TC utility script
# 2016-06-07 2.6 TC moodeOS 1.0
# 2016-08-28 2.7
# - RP (Richard Parslow): add code to set keyboard and varient
# - fix alsa mixer names with embedded spaces not parsed
# - add get-mixername
# - add quotes around amixname ($2 in get/set-alsavol)
# 2016-11-27 3.0 TC add install-kernel
#

if [[ $1 = "set-timezone" ]]; then
	ln -sf /usr/share/zoneinfo/$2 /etc/localtime
	exit
fi

if [[ $1 = "chg-name" ]]; then
	if [ $2 = "host" ]; then
		sed -i "s/$3/$4/" /etc/hostname
		sed -i "s/$3/$4/" /etc/hosts
	fi

	if [[ $2 = "browsertitle" ]]; then
		sed -i "s/<title>$3/<title>$4/" /var/www/header.php
	fi

	if [[ $2 = "upnp" ]]; then
		sed -i "s/friendlyname = $3/friendlyname = $4/" /etc/upmpdcli.conf
		sed -i "s/ohproductroom = $3/ohproductroom = $4/" /etc/upmpdcli.conf
	fi

	if [[ $2 = "dlna" ]]; then
		sed -i "s/friendly_name=$3/friendly_name=$4/" /etc/minidlna.conf
	fi

	if [[ $2 = "mpdzeroconf" ]]; then
		sed -i "s/zeroconf_name $3/zeroconf_name $4/" /etc/mpd.conf
	fi

	exit
fi

# card 0 = i2s or onboard, card 1 = usb 
# save alsa state after set-alsavol to support hotplug for card 1 USB audio device
if [[ $1 = "get-alsavol" || $1 = "set-alsavol" ]]; then
	TMP=$(cat /proc/asound/card1/id 2>/dev/null)
	if [[ $TMP = "" ]]; then CARD_NUM=0; else CARD_NUM=1; fi

	if [[ $1 = "get-alsavol" ]]; then
		# add quotes to sget $2 so mixer names with embedded spaces are parsed
		awk -F"[][]" '/%/ {print $2; count++; if (count==1) exit}' <(amixer -c $CARD_NUM sget "$2")
		exit
	else
		# set-alsavol
		amixer -c $CARD_NUM sset "$2" "$3%" >null
		
		# store alsa state if card 1 to preverve volume in case hotplug
		if [[ $CARD_NUM -eq 1 ]]; then
			alsactl store 1
		fi
			
		exit
	fi
fi

# get alsa mixer name for card1 (USB)
if [[ $1 = "get-mixername" ]]; then
	TMP=$(cat /proc/asound/card1/id 2>/dev/null)
	if [[ $TMP = "" ]]; then CARD_NUM=0; else CARD_NUM=1; fi

	awk -F"'" '/Simple mixer control/{print $2;}' <(amixer -c $CARD_NUM)
	exit
fi

# TC $1 = new theme color name, $2 = hex color value (light), $3 = hex color value (dark)
if [[ $1 = "alizarin" || $1 = "amethyst" || $1 = "bluejeans" || $1 = "carrot" || $1 = "emerald" || $1 = "fallenleaf" || $1 = "grass" || $1 = "herb" || $1 = "lavender" || $1 = "river" || $1 = "rose" || $1 = "turquoise" ]]; then
	# copy alizarin files
	cp /var/www/themes/alizarin/bootstrap-select.css /var/www/css
	cp /var/www/themes/alizarin/flat-ui.css /var/www/css
	cp /var/www/themes/alizarin/panels.css /var/www/css
	cp /var/www/themes/alizarin/indextpl.html /var/www/templates
	cp /var/www/themes/alizarin/jquery.knob.js /var/www/js

	# change to new theme color
	if [[ $1 != "alizarin" ]]; then
		# alizarin light color -> new color
		sed -i "s/e74c3c/$2/g" /var/www/css/bootstrap-select.css
		sed -i "s/e74c3c/$2/g" /var/www/css/flat-ui.css
		# alizarin dark color -> new color
		sed -i "s/c0392b/$3/g" /var/www/css/bootstrap-select.css
		sed -i "s/c0392b/$3/g" /var/www/css/flat-ui.css
		sed -i "s/c0392b/$3/g" /var/www/css/panels.css
		sed -i "s/c0392b/$3/g" /var/www/templates/indextpl.html
		sed -i "s/c0392b/$3/g" /var/www/js/jquery.knob.js
	fi

	# copy radio slider control image for the config pages
	cp /var/www/themes/$1-icon-on.png /var/www/images/toggle/icon-on.png
	cp /var/www/themes/$1-icon-on-2x.png /var/www/images/toggle/icon-on-2x.png
	exit
fi

if [[ $1 = "clear-syslogs" ]]; then
	truncate /var/log/alternatives.log --size 0
	truncate /var/log/apt/history.log --size 0
	truncate /var/log/apt/term.log --size 0
	truncate /var/log/auth.log --size 0
	truncate /var/log/bootstrap.log --size 0
	truncate /var/log/daemon.log --size 0
	truncate /var/log/debug --size 0
	truncate /var/log/dpkg.log --size 0
	truncate /var/log/faillog --size 0
	truncate /var/log/fsck/checkfs --size 0
	truncate /var/log/fsck/checkroot --size 0
	truncate /var/log/kern.log --size 0
	truncate /var/log/lastlog --size 0
	truncate /var/log/messages --size 0
	truncate /var/log/minidlna.log --size 0
	truncate /var/log/mpd/mpd.log --size 0
	truncate /var/log/nginx/access.log --size 0
	truncate /var/log/nginx/error.log --size 0
	#truncate /var/log/ntpstats/
	truncate /var/log/php5-fpm.log --size 0
	truncate /var/log/php_errors.log --size 0
	truncate /var/log/regen_ssh_keys.log --size 0
	truncate /var/log/samba/log.nmbd --size 0
	truncate /var/log/samba/log.smbd --size 0
	truncate /var/log/syslog --size 0
	truncate /var/log/user.log --size 0
	truncate /var/log/wtmp --size 0
	#truncate /var/log/moode.log --size 0
	exit
fi

if [[ $1 = "clear-playhistory" ]]; then
	TIMESTAMP=$(date +'%Y%m%d %H%M%S')
	LOGMSG=" Log initialized"
	echo $TIMESTAMP$LOGMSG > /var/www/playhistory.log
	exit
fi

# card 0 = i2s or onboard, card 1 = usb
if [[ $1 = "unmute-default" ]]; then
    amixer scontrols | sed -e 's/^Simple mixer control//' | while read line; do
        amixer -c 0 sset "$line" unmute;
        amixer -c 1 sset "$line" unmute;
        done
    exit
fi

# unmute IQaudIO Pi-AMP+, Pi-DigiAMP+
if [[ $1 = "unmute-pi-ampplus" || $1 = "unmute-pi-digiampplus" ]]; then
	echo "22" >/sys/class/gpio/export
	echo "out" >/sys/class/gpio/gpio22/direction
	echo "1" >/sys/class/gpio/gpio22/value	
	exit
fi

# set keyboard and varient
if [[ $1 = "set-keyboard" ]]; then
    debconf-set-selections <<< "keyboard-configuration keyboard-configuration/layoutcode string $2"
    debconf-set-selections <<< "keyboard-configuration keyboard-configuration/xkb-keymap select $2"
    exit
fi

if [[ $1 = "set-keyboard-variant" ]]; then
    debconf-set-selections <<< "keyboard-configuration keyboard-configuration/variant select $2"
    exit
fi

# install Linux kernel
if [[ $1 = "install-kernel" ]]; then
	# check kernel name
	if [[ -z "$2" || $2 != "Standard" && $2 != "Advanced" ]]; then
		echo "valid kernels are: Standard, Advanced"
		exit
	fi

	# Install kernel
	echo "installing $2 kernel"
	cd /
	cp /boot/config.txt /
	cp /boot/cmdline.txt /
	rm -rf /lib/modules /lib/firmware /boot/*
	tar xfz /var/www/kernels/$2/modules.tar.gz
	tar xfz /var/www/kernels/$2/firmware.tar.gz
	tar xfz /var/www/kernels/$2/boot.tar.gz
	mv /config.txt /boot
	mv /cmdline.txt /boot
	
	# Flush cached disk writes
	echo "flushing cached disk writes"
	sync

	echo "$2 kernel installed"
	exit
fi
