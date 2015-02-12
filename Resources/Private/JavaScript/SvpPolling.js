/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Streamovations Video Player Polling (SVPP)
 *
 * @package streamovations_vp
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
var SvpPolling = (function($) {

	// internal methods and properties
	var intervalId = null,
		scriptPath = null,
		// successive fail counts
		failCount = 0,
		limitFailCount = 5;

	function poll(force) {
		var url = scriptPath + (force === true ? '&force=1' : '');

		// setInterval operates in a global scope, so never refer to this!
		$.get(url, function(data) {
			if (!$.isEmptyObject(data)) {
				SvpStarter.processMeetingdataChange(data);
			}
			failCount = 0;
		}, 'json').fail(function() {
			failCount++;
			if (failCount >= limitFailCount) {
				console.log('SVPP | ERROR: ' + failCount + ' successive polling failures')
				_this.stop();
				failCount = 0;
			}
		});
	}

	// @TODO when everything works, divide internal and external methods in SVPS as well
	// actual SVPP object
	var _this = {
		init: function(hash, pid, interval) {
			scriptPath = document.baseURI + 'index.php?id=' + pid + '&eID=streamovations_vp_meetingdata' + '&hash=' + hash;
			if (intervalId === null) {
				console.log('SVPP | Polling started');
				// @LOW what about a setTimeout instead, iterating it in poll.$.get.done method?
				intervalId = setInterval(function() {
					poll(false);
				}, interval);
				// for immediate (first) polling
				poll(true);
			}
		},

		stop: function() {
			if (intervalId !== null) {
				clearInterval(intervalId);
				intervalId = null;
				console.log('SVPP | Polling stopped');
			}
		}
	}

	return _this;
})(jQuery);