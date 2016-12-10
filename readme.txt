=================================================================
SETUP GUIDE

Moode Audio Player, (C) 2014 Tim Curtis
http://moodeaudio.org

Updated: 2016-11-27
=================================================================

GENERAL INFORMATION

- Use http://moode, moode.local or IP address
- Access point IP address is 172.24.1.1
- SSH login userid=pi, pwd=raspberry
- Preface all commands with sudo
- Samba shares are NAS, RADIO, SDCARD and USB

WIFI ADAPTERS THAT SUPPORT ACCESS POINT MODE

- Raspberry Pi 3 integrated WiFi adapter
- Canakit WiFi Adapter
- Raspberry Pi USB WiFi Dongle
- WiFi adapters based on RTL RT5370 chipset
- Edimax EW-7811Un (requires kernel 4.4.19 or higher)

IN-PLACE SOFTWARE UPDATES

- Updates to Moode software are made available periodically and can be downloaded and
  installed from within Moode itself by clicking "CHECK for software update" on the 
  System config screen.
- Click VIEW to see a list of what is contained in the update package
- Click INSTALL to download and install the update package

SETUP INSTRUCTIONS

 1. INITIAL SETUP
    a) insert sd card
    b) connect USB or I2S audio device
    c) connect USB storage devices
    - Ethernet mode
    a) insert ethernet cable
    b) power on
    c) http://moode
    - Access Point (AP) mode
    a) insert WiFi adapter that supports AP mode
    b) power on
    c) join network SSID=Moode, pwd=moodeaudio
    d) http://moode.local

 2. AUDIO DEVICE SETUP
    - USB DEVICE
    a) Menu, Configure, MPD
    b) leave Volume control set to "Software"
    c) set Audio output to "USB audio device" then press APPLY
    - I2S DEVICE
    a) Menu, Configure, Audio
    b) select an I2S audio device then press SET
    c) reboot
    d) Menu, Configure, MPD
    e) leave Volume control set to "Software"
    f) verify Audio device is set to "I2S audio device" then press APPLY

 3. TIME ZONE AND AUDIO DEVICE DESCRIPTION 
    a) Menu, Configure, System
    b) select appropriate timezone then press SET
    c) Menu, Customize
    d) scroll down to Audio device description and select a device. The entry is for display on the 
       Audio info screen. If a particular device is not listed then select "USB audio device". Note
       that I2S devices are automatically populated.

 4. ADD SOURCE(S) CONTAINING MUSIC FILES
    - USB AND SDCARD STORAGE DEVICE
    a) Menu, Configure, Sources
    b) press UPDATE MPD DATABASE button
    c) WAIT for completion (no spinner on the Browse tab)
    d) click Browse tab. If more folders appear than those containing music then restart MPD
    - NAS DEVICE
    a) Menu, Configure, Sources
    b) click NEW button to configure a music source (MPD DB update initiates automatically after SAVE)
    c) WAIT for completion (no spinner on the Browse tab)
    d) click Browse tab. If more folders appear than those containing music, restart MPD

 5. VERIFY AUDIO PLAYBACK
    - Ethernet mode
    a) http://moode
    b) Click a radio station from the Playlist
    - AP mode
    a) http://moode.local
    b) Browse, SDCARD, Stereo Test
	c) Menu for "LR Channel And Phase" track
	d) Play

 At this point a FULLY OPERATIONAL PLAYER exists.

=================================================================
CUSTOM CONFIGS

Customize the player by using any of the following procedures.
=================================================================

 1. CONFIGURE FOR WIFI CONNECTION
    - Ethernet mode
    a) leave eth cable connected
    b) insert wifi adapter (while Pi running)
    c) http://moode
    d) Menu, Configure, Network
    e) configure a wifi connection
    f) Menu, Restart, Power off
    g) unplug eth cable
    h) power on
    - Access Point (AP) mode
    a) join network SSID=Moode, pwd=moodeaudio
    b) http://moode.local
    c) Menu, Configure, Network
    d) configure a wifi connection
    e) Menu, Restart, Reboot

 2. CHANGE HOST AND SERVICE NAMES
    a) Menu, Configure, System (and Audio)
    b) Press SET after entering appropriate value in each name field
    c) reboot is required if changing Host name and/or Browser title

 3. AUTO-CONFIGURE
    a) change values in the file below
    b) paste contents into /boot/moodecfg.txt
    c) sudo reboot
    d) join SSID if AP mode
    e) http://hostname.local
 
#########################################
# Copy this file to /boot/moodecfg.txt
# worker will process it at startup then
# delete it and automatically reboot.
#########################################

[names]
hostname=moode
browsertitle=MoOde Player
airplayname=Moode Airplay
upnpname=Moode UPNP
dlnaname=Moode DLNA
mpdzeroconf=moode

[services]
airplaysvc=0
upnpsvc=0
dlnasvc=0

[network]
wlanssid=MySSID
wlansec=wpa
wlanpwd=MyPassword
apdssid=Moode
apdchan=6
apdpwd=moodeaudio

[other]
timezone=America/Detroit
themecolor=Emerald

=================================================================
AFTER PLAYER SETUP

Follow these instructions for making certain types of changes
=================================================================

 1. Switching from USB to I2S audio device
    a) unplug USB audio device
    b) Menu, Restart, Power off 
    c) install I2S audio device
    d) power on
    e) Menu, Configure, Audio
    f) select appropriate I2S audio device then press SET
    g) Menu, Restart, Reboot
    h) Menu, Configure, MPD
    i) Verify Audio output set to "I2S audio device"
    j) press APPLY

 2. Switching from I2S to USB audio device
    a) Menu, Configure, Audio
    b) select "None" for I2S audio device then press SET
    c) Menu, Restart, Power off 
    d) optionally unplug I2S audio device
    e) plug in USB audio device
    f) power on
    g) Menu, Configure, MPD
    h) Select "USB audio device" for Audio output
    i) press APPLY

 3. Switching from WiFi back to Ethernet
    a) plug in Ethernet cable 
    b) Menu, Configure, Network 
    c) click RESET network configuration to defaults
    d) Menu, Restart, Power off 
    e) Remove WiFi adapter 
    f) power on
 
=================================================================
END SETUP GUIDE
=================================================================
