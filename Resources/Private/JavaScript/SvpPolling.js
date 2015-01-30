// #@TODO license + author info
/*
 * Streamovations Video Player Polling (SVPP)
 * ------------------------------------------
 */
var SvpPolling = (function($) {

	// internal methods and properties
	var intervalId = null,
		scriptPath = null,
		// successive fail counts
		failCount = 0,
		// @TODO set to at least 5
		limitFailCount = 1;

	function poll() {
		// setInterval operates in a global scope, so never refer to this!
		$.get(scriptPath, function(data) {
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
				// @LOW what about a setTimeout instead, iterating it in poll.$.get.done method?
				intervalId = setInterval(poll, interval);
				console.log('SVPP | Polling started');
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