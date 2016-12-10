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
# 2016-06-07 2.6 TC resize root file system
#

if [[ -z $1 ]]; then
	echo "resizefs: missing arg <start>"
	exit
fi

if [[ $1 != "start" ]]; then
	echo "resizefs: valid arg is <start>"
	exit
fi

# max size of sd card
PART_END=""
# 2.5GB (2X size of stock j-lite)
#PART_END=5316607
# 2.0GB (1.5X size of stock j-lite)
#PART_END=3987457
# 1.5GB (1.25X size of stock j-lite)
#PART_END=3323903

get_init_sys() {
	if command -v systemctl > /dev/null && systemctl | grep -q '\-\.mount'; then
		SYSTEMD=1
	elif [ -f /etc/init.d/cron ] && [ ! -h /etc/init.d/cron ]; then
		SYSTEMD=0
	else
		echo "Unrecognised init system"
		exit 1
	fi
}

get_init_sys
if [ $SYSTEMD -eq 1 ]; then
	ROOT_PART=$(mount | sed -n 's|^/dev/\(.*\) on / .*|\1|p')
else
	if ! [ -h /dev/root ]; then
	  echo "/dev/root does not exist or is not a symlink. Don't know how to expand" 20 60 2
	  exit 0
	fi
	ROOT_PART=$(readlink /dev/root)
fi

PART_NUM=${ROOT_PART#mmcblk0p}
if [ "$PART_NUM" = "$ROOT_PART" ]; then
	echo "$ROOT_PART is not an SD card. Don't know how to expand" 20 60 2
	exit 0
fi

# NOTE: the NOOBS partition layout confuses parted. For now, let's only 
# agree to work with a sufficiently simple partition layout
if [ "$PART_NUM" -ne 2 ]; then
	echo "Your partition layout is not currently supported by this tool. You are probably using NOOBS, in which case your root filesystem is already expanded anyway." 20 60 2
	exit 0
fi

LAST_PART_NUM=$(parted /dev/mmcblk0 -ms unit s p | tail -n 1 | cut -f 1 -d:)
if [ $LAST_PART_NUM -ne $PART_NUM ]; then
	echo "$ROOT_PART is not the last partition. Don't know how to expand" 20 60 2
	exit 0
fi

# Get the starting offset of the root partition
PART_START=$(parted /dev/mmcblk0 -ms unit s p | grep "^${PART_NUM}" | cut -f 2 -d: | sed 's/[^0-9]//g')
[ "$PART_START" ] || exit 1
# Return value will likely be error for fdisk as it fails to reload the
# partition table because the root fs is mounted
fdisk /dev/mmcblk0 <<EOF
p
d
$PART_NUM
n
p
$PART_NUM
$PART_START
$PART_END
p
w
EOF

# Set up an init.d script
cat <<EOF > /etc/init.d/resize2fs_once &&
#!/bin/sh
### BEGIN INIT INFO
# Provides:          resize2fs_once
# Required-Start:
# Required-Stop:
# Default-Start: 3
# Default-Stop:
# Short-Description: Resize the root filesystem to fill partition
# Description:
### END INIT INFO

. /lib/lsb/init-functions

case "\$1" in
	start)
	    log_daemon_msg "Starting resize2fs_once" &&
	    resize2fs /dev/$ROOT_PART &&
	    update-rc.d resize2fs_once remove &&
	    rm /etc/init.d/resize2fs_once &&
	    log_end_msg \$?
	    ;;
	*)
	    echo "Usage: \$0 start" >&2
	    exit 3
	    ;;
esac
EOF

chmod +x /etc/init.d/resize2fs_once &&
update-rc.d resize2fs_once defaults &&
echo "Root partition has been resized.\nThe filesystem will be enlarged upon the next reboot"

echo "SYSTEMD "$SYSTEMD
echo "ROOT_PART "$ROOT_PART
echo "PART_NUM "$PART_NUM
echo "LAST_PART_NUM "$LAST_PART_NUM
echo "PART_START "$PART_START
echo "PART_END "$PART_END

