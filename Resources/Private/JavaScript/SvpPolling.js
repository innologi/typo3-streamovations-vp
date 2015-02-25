/**
 * Streamovations Video Player Polling (SVPP)
 *
 * @package streamovations_vp
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
var SvpPolling = (function($) {

	'use strict';

	/**
	 * Interval identifier
	 *
	 * @var int
	 */
	var intervalId = null;

	/**
	 * Path to polling script
	 *
	 * @var string
	 */
	var scriptPath = null;

	/**
	 * Successive fail counts
	 *
	 * @var int
	 */
	var failCount = 0;

	/**
	 * Limit of fail count, after which polling stops
	 *
	 * @var int
	 */
	var limitFailCount = 5;

	/**
	 * Log messages
	 *
	 * @var object
	 */
	var logMsg = {
		poll_start: 'Polling started',
		poll_stop: 'Polling stopped',
		fail_limit: 'successive polling failures'
	};

	/**
	 * Logs message to console, and allows to differentiate between errors and info
	 *
	 * @param message string Message to log
	 * @param error boolean Set true if error message, otherwise false
	 * @return void
	 */
	function log(message, error) {
		console.log('SVPP | ' + message);
		if (error) {
			// @LOW introduce some frontend messaging library for all these logs?
		}
	}

	/**
	 * Executes polling script path once
	 *
	 * @param force boolean If true, sets force=1 parameter
	 * @return void
	 */
	function poll(force) {
		var url = scriptPath + (force === true ? '&force=1' : '');

		// setInterval operates in a global scope, so never refer to 'this'!
		$.get(url, function(data) {
			if (!$.isEmptyObject(data)) {
				SvpStarter.processMeetingdataChange(data);
			}
			failCount = 0;
		}, 'json').fail(function() {
			failCount++;
			if (failCount >= limitFailCount) {
				log(failCount + ' ' + logMsg.fail_limit, true);
				SVPP.stop();
				failCount = 0;
			}
		});
	}


	/**
	 * Actual SVPP object, offers public methods/properties
	 *
	 * @var object
	 */
	var SVPP = {

		/**
		 * Initializes polling
		 *
		 * @param hash string Hash of session event
		 * @param pid int Polling page ID
		 * @param interval int Polling interval in seconds
		 * @return void
		 */
		init: function(hash, pid, interval) {
			scriptPath = document.baseURI + 'index.php?id=' + pid + '&eID=streamovations_vp_meetingdata' + '&hash=' + hash;
			if (intervalId === null) {
				log(logMsg.poll_start, false);
				// @LOW what about a setTimeout instead, iterating it in poll.$.get.done method?
				intervalId = setInterval(function() {
					poll(false);
				}, interval);
				// for immediate (first) polling
				poll(true);
			}
		},

		/**
		 * Stops polling
		 *
		 * @return void
		 */
		stop: function() {
			if (intervalId !== null) {
				clearInterval(intervalId);
				intervalId = null;
				log(logMsg.poll_stop, false);
			}
		}
	};

	return SVPP;
})(jQuery);