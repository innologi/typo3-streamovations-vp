// #@TODO license + author info
/*
 * Streamovations Video Player Starter (SVPS)
 * ------------------------------------------
 *
 * Please note that MOST properties are for internal use only,
 * any property that is configurable will have a respective
 * TypoScript setting.
 */
var SvpStarter = (function($) {

	/**
	 * Speaker-avatar image directory, configured via TS
	 *
	 * @var string
	 */
	var speakerImageDir = '###SPEAKER_IMAGE_DIR###';

	/**
	 * Interval for polling script SVPP, configured via TS
	 *
	 * @var int
	 */
	var pollingInterval = parseInt('###POLLING_INTERVAL###');

	/**
	 * Current TYPO3 page ID, provided via TS
	 *
	 * @var int
	 */
	var currentPage = parseInt('###CURRENT_PAGE_ID###');

	/**
	 * Counter of processed meetingdata elements (live streams)
	 *
	 * @var object
	 */
	var count = {
		topic: 0,
		speaker: 0
		//topicTime: 0,
		//speakerTime: 0
	};

	// extend jQuery
	$.fn.exists = function() {
		return this.length !== 0;
	}

	/**
	 * Initialize live counters, keeping track of the number
	 * of speakers, topics, etc.
	 *
	 * @return void
	 */
	function initLiveCounters() {
		var $container = $('.' + _this.select.container);
		count.topic = $('.topics .topic', $container).length;
		count.speaker = $('.speakers .speaker', $container).length;
		// timeline's are produced via polling, so no need to initialize them here
	}

	/**
	 * Initialize polling, detects and starts SVPP.
	 * Is disabled when polling interval is set to 0.
	 *
	 * @return void
	 */
	function initPolling() {
		if (pollingInterval > 0) {
			if (typeof(SvpPolling) !== 'undefined') {
				var interval = pollingInterval * 1000,
					hash = $('#' + _this.select.data).attr('data-hash');

				this.player.onPlay(function() {
					SvpPolling.init(hash, currentPage, interval);
				});
				this.player.onPause(function() {
					SvpPolling.stop();
					_this.deactivateElement('speaker');
					_this.deactivateElement('topic');
				});
			} else {
				log('SVPP not loaded, polling inactive', true);
			}
		} else {
			log('Polling disabled', false);
		}
	}

	/**
	 * Activates latest element in an array
	 *
	 * @param array array Contains elements
	 * @param type string The element-type [speaker,topic]
	 * @return void
	 */
	function activateLatestElement(array, type) {
		var id = array[array.length-1].id;
		if (_this.active[type] !== id) {
			_this.activateElement(id, type);
		}
	}

	/**
	 * Adds a new type-element to the appropriate DOM location
	 *
	 * @param array array Contains new element-properties
	 * @param type string The element-type [speaker,topic]
	 * @return void
	 */
	function addNewElements(array, type) {
		// @TODO what if there is no template?
		var $template = $('.' + _this.select.container + ' .' + type + 's .' + type).last();
		// @TODO if we replace insertAfter() with something like add(), can we put the for-order back to normal?
		for (var i=array.length-1; i >= 0; i--) {
			var $temp = $template.clone(),
				elem = array[i];
			// some changes are too specific to be handled generally
			switch (type) {
				case 'speaker':
					$('.speaker-avatar', $temp).attr('src', speakerImageDir + elem.photo);
					$('.speaker-data', $temp).html(elem.firstname + ' ' + elem.lastname);
					break;
				case 'topic':
					$('.topic-title', $temp).html(elem.title);
					$('.topic-description', $temp).html(elem.description);
			}
			$temp.attr('data-' + type, elem.id)
				// $template might have been active, so disable just in case
				.removeClass('active')
				.insertAfter($template);

			count[type].length++;
		}
	}

	/**
	 * Logs message to console, and allows to differentiate between errors and info
	 *
	 * @param message string Message to log
	 * @param error boolean Set true if error message, otherwise false
	 * @return void
	 */
	function log(message, error) {
		console.log('SVPS | ' + message);
		if (error) {
			// @LOW introduce some frontend messaging library for all these logs? especially in jumpToTopic()!
		}
	}

	// actual SVPS object
	var _this = {

		// wrapper to substitute the actual player object
		player: null,
		// wrapper to substitute jwplayer object for jwplayer-only calls
		jw: null,
		// consistent property determining if current item is live or not (vod)
		isLiveStream: false,

		// #@LOW make these ids/class configurable?
		// selector strings
		select: {
			// id of player HTML element
			player: 'tx-streamovations-vp-play',
			// id of player data HTML element
			data: 'tx-streamovations-vp-playerdata',
			// id of topic timeline HTML element
			topicTimeline: 'tx-streamovations-vp-topictimeline',
			// id of speaker timeline HTML element
			speakerTimeline: 'tx-streamovations-vp-speakertimeline',
			// container class of module
			container: 'tx-streamovations-vp'
		},

		// @LOW better naming, e.g. topicTime
		// map of id's
		idMap: {
			playlist: {},
			topic: {},
			speaker: {}
		},

		// currently active data types
		active: {
			topic: 0,
			speaker: 0
		},

		// original property references, for those we need to overrule
		orig: {
			player: {
				next: null,
				previous: null,
				setQualityLevel: null,
				setAudioLanguage: null
			}
		},

		// callback arrays for each event handler
		callbacks: {
			onTime: [],
			onSeek: [],
			onPlay: [],
			onPause: []
		},

		// used to limit seekOnPlays to 1
		seekOnPlay: {},

		// registers a hit of onSeek events, to determine if topics/speakers need to be disabled
		onSeekHit: {
			topic: false,
			speaker: false
		},

		// init / start function
		init: function() {
			// check if necessary elements exist
			var $player = $('#' + this.select.player),
				$data = $('#' + this.select.data).first();
			if (!$player.exists() || !$data.exists()) {
				// at least one of necessary elements does not exist
				log('The player element or player data is not available', true);
				return false;
			}

			// @TODO try/catch for every JSON.parse call?
			// read & parse data
			var data = JSON.parse($data.html().trim());
			$data.html('');
			if (typeof(data) !== 'object') {
				log('Player data is invalid or in an unsupported format', true)
				return false;
			}

			switch ('###PLAYER_TYPE###') {
				case '2':
					if (!this.createSmvPlayer(data)) {
						return false;
					}
					break;
				case '1':
					if (!this.createJwPlayer(data)) {
						return false
					}
					break;
				default:
					// no valid player configuration
					log('No supported player configured', true);
					return false;
			}

			// meetingdata refers to streamfile id's, but we can only request playlist id from jwplayer object
			if (data.hasOwnProperty('playlist')) {
				this.pushPlaylistToIdMap(data.playlist);
			}
			if (data.hasOwnProperty('application')) {
				this.isLiveStream = data.application === 'rtplive';
			}

			if (this.isLiveStream) {
				initLiveCounters();
				initPolling();
			} else {
				this.initEventHandlers();
			}
			return true;
		},

		// initialize JW Player
		initJwPlayer: function(requireLicense) {
			var licenseKey = '###JWPLAYER_KEY###';
			if (typeof(jwplayer) === 'undefined') {
				log('No jwplayer loaded', true);
				return false;
			}

			if (licenseKey) {
				jwplayer.key = licenseKey;
			} else if(requireLicense) {
				log('A jwplayer license key is required', true);
				return false;
			}
			return true;
		},

		// initialize Streamovations Player
		createSmvPlayer: function(data) {
			if (this.initJwPlayer(true)) {
				// smvplayer object needs to exist
				if (typeof(smvplayer) !== 'undefined') {

					this.player = smvplayer(this.select.player);
					this.player.init(data);
					this.jw = jwplayer(this.select.player);

					// Smvplayer calls jwplayer.remove() on moving in the playlist, which clears the entire jwplayer
					// instance, including event handlers, so we need a construct that reassigns this.jw and
					// all of the event handler callbacks. This is what reset() is for.
					this.player.onTime = function(callback) {
						_this.callbacks.onTime.push(callback);
						_this.jw.onTime(callback);
					}
					this.player.onSeek = function(callback) {
						_this.callbacks.onSeek.push(callback);
						_this.jw.onSeek(callback);
					}
					this.player.onPlay = function(callback) {
						_this.callbacks.onPlay.push(callback);
						_this.jw.onPlay(callback);
					}
					this.player.onPause = function(callback) {
						_this.callbacks.onPause.push(callback);
						_this.jw.onPause(callback);
					}
					// overrule player.next()
					this.orig.player.next = this.player.next;
					this.player.next = function() {
						_this.orig.player.next();
						_this.reset();
					}
					// overrule player.previous()
					this.orig.player.previous = this.player.previous;
					this.player.previous = function() {
						_this.orig.player.previous();
						_this.reset();
					}
					// overrule player.setQualityLevel()
					this.orig.player.setQualityLevel = this.player.setQualityLevel;
					this.player.setQualityLevel = function(q) {
						_this.orig.player.setQualityLevel(q);
						_this.reset();
					}
					// overrule player.setAudioLanguage()
					this.orig.player.setAudioLanguage = this.player.setAudioLanguage;
					this.player.setAudioLanguage = function(l) {
						_this.orig.player.setAudioLanguage(l);
						_this.reset();
					}
					// because smvplayer doesnt use the playlist as jwplayer does, we emulate
					// some specific playlist methods on this.player to create a shared api
					// where this is covenient for SVPS
					this.player.getPlaylistIndex = function() {
						return _this.player.getTimeline().currentItem;
					}
					this.player.playlistNext = function() {
						_this.player.next();
					}
					this.player.playlistPrev = function() {
						_this.player.previous();
					}
					this.player.playlistItem = function(index) {
						moveAction = index - _this.player.getPlaylistIndex();
						if (moveAction > 0) {
							_this.recursiveMoveNext(0, moveAction);
						} else if(moveAction < 0) {
							_this.recursiveMovePrevious(0, moveAction);
						}
					}

					return true;
				}

				log('No smvplayer loaded', true);
			}
			return false;
		},

		// initialize JW Player
		createJwPlayer: function(data) {
			if (this.initJwPlayer(false)) {
				jwplayer(this.select.player).setup(data);
				// apparently setup() creates a new object, so assign these AFTER setup()
				this.player = jwplayer(this.select.player);
				this.jw = this.player;

				// on changing playlist item, deactivate elements
				this.player.onPlaylistItem(function(e) {
					_this.deactivateElement('speaker');
					_this.deactivateElement('topic');
				});
				return true;
			}
			return false;
		},

		// initialize Native HTML5 Player
		createNativeHtml5Player: function() {
			// #@LOW finish implementation?
			if (!!document.createElement('video').canPlayType) {
				// fallback
			}
			log('ERROR: unfinished implementation', true);
			return false;
		},

		// meetingdata refers to streamfile id's, but we can only request playlist id from jwplayer object
		pushPlaylistToIdMap: function(data) {
			for(var id in data) {
				var p = data[id];
				this.idMap.playlist[p.streamfileId] = parseInt(id)
			}
		},

		// prepare all eventhandlers
		initEventHandlers: function() {
			log('initializing event handlers', false);

			// set jump event on topic clicks
			$('.' + this.select.container + ' .topics').on('click', '.topic .topic-link', function(e) {
				e.preventDefault();
				_this.jumpToTopic(
					$(this).parent('.topic').attr('data-topic')
				);
			});

			// parse meeting data
			var $topicTimeline = $('#' + this.select.topicTimeline).first();
			var $speakerTimeline = $('#' + this.select.speakerTimeline).first();

			if ($topicTimeline.exists()) {
				// @TODO try/catch for every JSON.parse call?
				var timeline = JSON.parse($topicTimeline.html());
				if (timeline.length > 0) {
					this.createTimelineEventHandlers('topic', timeline, true);
				}
				$topicTimeline.html('');
			}
			if ($speakerTimeline.exists()) {
				// @TODO try/catch for every JSON.parse call?
				var timeline = JSON.parse($speakerTimeline.html());
				if (timeline.length > 0) {
					this.createTimelineEventHandlers('speaker', timeline, false);
				}
				$speakerTimeline.html('');
			}
		},

		// processes timeline data to create the proper event handler callbacks
		createTimelineEventHandlers: function(elemType, timeline, pushToIdMap) {
			for (var i=0; i<timeline.length; i++) {
				var time = timeline[i],
					j = i+1;
				time.start = Math.floor(time.relativeTime / 1000);
				time.end = j < timeline.length && time.streamfileId === timeline[j].streamfileId
					? Math.floor(timeline[j].relativeTime / 1000)
					: Number.MAX_SAFE_INTEGER;

				if (pushToIdMap) {
					this.idMap[elemType][time.id] = {
						playlist: this.idMap.playlist[time.streamfileId],
						time: time.start
					}
				}

				// @TODO what if we do callbacks per playlist item?
				// set events on times
				this.player.onTime((function(t, type) {
					return function(e) {
						if (e.position >= t.start && e.position < (t.start+0.4)
							&& t.id !== _this.active[type]
							&& _this.timeIsOnPlaylist(t)
						) {
							_this.activateElement(t.id, type);
						}
					};
				})(time, elemType));

				// set events on seeks
				this.player.onSeek((function(t, type) {
					return function(e) {
						if (e.offset >= t.start && e.offset < t.end
							&& _this.timeIsOnPlaylist(t)
						) {
							if (t.id !== _this.active[type]) {
								_this.activateElement(t.id, type);
							}
							// even if t.id === this.active.type, onSeekHit needs to be marked to prevent deactivation
							_this.onSeekHit[type] = true;
						}
					}
				})(time, elemType));
			}

			// final onseek deactivates actives if no previous onseek had a hit
			this.player.onSeek((function(type) {
				return function(e) {
					if (!_this.onSeekHit[type] && _this.active[type] !== 0) {
						_this.deactivateElement(type);
					}
					_this.onSeekHit[type] = false;
				}
			})(elemType));
		},

		/**
		 * Process a meetingdata-change as retrieved by polling.
		 *
		 * Note that this method assumes meetingdata is only added,
		 * never removed!
		 *
		 * @param data object Parsed JSON object
		 * @return void
		 */
		processMeetingdataChange: function(data) {
			// note that empty/null elements are translated by json-encoding to
			// value "false" in some cases, hence these extra checks

			// add missing speakers?
			if (data.hasOwnProperty('speakers')
				&& data.speakers !== false
				&& data.speakers.length > count.speaker
			) {
				addNewElements(
					data.speakers.slice(count.speaker),
					'speaker'
				);
			}
			// add missing topics?
			if (data.hasOwnProperty('topics')
				&& data.topics !== false
				&& data.topics.length > count.topic
			) {
				addNewElements(
					data.topics.slice(count.topic),
					'topic'
				);
			}
			// activate latest speaker timestamp?
			if (data.hasOwnProperty('speakerTimeline')
				&& data.speakerTimeline !== false
				&& data.speakerTimeline.length > 0
			) {
				activateLatestElement(data.speakerTimeline, 'speaker');
			}
			// activate latest topic timestamp?
			if (data.hasOwnProperty('topicTimeline')
				&& data.topicTimeline !== false
				&& data.topicTimeline.length > 0
			) {
				activateLatestElement(data.topicTimeline, 'topic');
			}
		},

		// needs to be called when the jwplayer instance was removed and a new instance created
		reset: function() {
			this.jw = jwplayer(this.select.player);
			// if really new, an onReady will be fired
			this.jw.onReady(function(e) {
				_this.deactivateElement('speaker');
				_this.deactivateElement('topic');
				// all event handler callbacks need to be re-attached
				_this.reattachEventCallbacks();
			});
		},

		// reattach callbacks on event handlers, if stored in respective arrays
		reattachEventCallbacks: function() {
			for (var c in this.callbacks.onTime) {
				this.jw.onTime(this.callbacks.onTime[c]);
			}
			for (var c in this.callbacks.onSeek) {
				this.jw.onSeek(this.callbacks.onSeek[c]);
			}
			for (var c in this.callbacks.onPlay) {
				this.jw.onPlay(this.callbacks.onPlay[c]);
			}
			for (var c in this.callbacks.onPause) {
				this.jw.onPause(this.callbacks.onPause[c]);
			}
			log('Reattached event callbacks', false);
		},

		// deactivate all topics/speakers
		deactivateElement: function(type) {
			$('.' + this.select.container + ' .' + type + 's .' + type + '.active').removeClass('active');
			this.active[type] = 0;
		},

		// activate topic/speaker
		activateElement: function(id, type) {
			this.deactivateElement(type);
			$('.' + this.select.container + ' .' + type + 's .' + type + '[data-' + type + '=' + id + ']').first().addClass('active');
			this.active[type] = id;
			log('Activated ' + type + ' ' + id, false);
		},

		// jump to the correct topic after finding its playlist item
		jumpToTopic: function(id) {
			var topic = this.idMap.topic[id];
			if (topic !== undefined) {
				if (topic.playlist !== this.player.getPlaylistIndex()) {
					this.player.playlistItem(topic.playlist);
				}
				// e.g. when IDLE (smv) or BUFFERING (jw)
				var state = this.jw.getState();
				if (state !== 'PLAYING') {
					// not all relevant onSeek events will trigger if player hasn't started
					this.applySeekOnPlay(topic);
					this.jw.play(true);
				} else {
					this.player.seek(topic.time);
				}
			} else {
				log('Topic has no registered timestamps', true);
			}
		},

		// recursively call playlistNext(), smvplayer only!
		recursiveMoveNext: function(current, limit) {
			this.player.playlistNext();
			current++;
			if (current < limit) {
				this.jw.onReady(function (e) {
					// timeout prevents flash from crashing
					setTimeout(function() {
						_this.recursiveMoveNext(current, limit);
					}, 50);
				});
			}
		},

		// recursively call playlistPrev(), smvplayer only!
		recursiveMovePrevious: function(current, limit) {
			this.player.playlistPrev();
			current--;
			if (current > limit) {
				this.jw.onReady(function (e) {
					// timeout prevents flash from crashing
					setTimeout(function() {
						_this.recursiveMovePrevious(current, limit);
					}, 50);
				});
			}
		},

		// applies a seek() on the onPlay event handler, useful when player isn't playing
		// otherwise, the flash player may crash (argh)
		applySeekOnPlay: function(o) {
			if (!this.seekOnPlay.hasOwnProperty(o.playlist)) {
				this.seekOnPlay[o.playlist] = {};
			}
			this.seekOnPlay[o.playlist][o.time] = true;

			// note that this onPlay isn't added to callbacks with smvPlayer, that would be useless
			this.jw.onPlay(function (e) {
				// there's no way to delete onPlay event callbacks.. so we need these conditions :(
				if (_this.seekOnPlay[o.playlist][o.time]) {
					_this.player.seek(o.time);
					_this.seekOnPlay[o.playlist][o.time] = false;
				}
			});
		},

		// find out if time is valid for current playlist item
		timeIsOnPlaylist: function(t) {
			return this.idMap.playlist[t.streamfileId] === this.player.getPlaylistIndex();
		}
	}

	// initialize when document is loaded
	$(document).ready(function() {
		// @TODO this should be done outside of this script via SVPS.init();
		_this.init();
	});

	return _this;
})(jQuery);