Moode Audio Player
==================

This is a non official git repo for Moode Audio Player from [moodeaudio.org](http://moodeaudio.org), (C) 2014 Tim Curtis

The goal of this repo, is to track upstream changes from version to version,
and provide an easy way to submit new features to upstream. Or reapply them after upgrade.

You can read more from [readme.txt](https://github.com/atoomic/moodeaudio/blob/master/readme.txt) file.

This Branch:
------------

This branch provides a simple "sleep bed timer" implementation, added to the UI using a "Sleep timer" menu.
You can select one of the predefined values
- 15 minutes
- 30 minutes
- 45 minutes
- 1 hour
- 2 hours

Or also cancel any previously timer.
This is mainly using a simple sleep + stop command like this:

			sleep Xm
			mpc stop

Todo:
-----
- [ ] better css / style for the modal timer window
- [ ] clean previous timers when setting one
- [ ] free timer value ?