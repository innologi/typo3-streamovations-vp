/**
 * Streamovations Video Player Starter (SVPS)
 *
 * @package streamovations_vp
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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
	 * Player type, configured via TS
	 *
	 * @var int
	 */
	var playerType = parseInt('###PLAYER_TYPE###');

	/**
	 * Determines if meetingdata-type is enabled, configured via TS
	 *
	 * @var object
	 */
	var meetingdata = {
		topic: parseInt('###MEETINGDATA_TOPICS###'),
		speaker: parseInt('###MEETINGDATA_SPEAKERS###')
	}

	/**
	 * Maps id's of objects to value or object
	 *
	 * @var object
	 */
	var idMap = {
		// streamfile id: playlist id
		playlist: {},
		// topic id: topic obj
		topic: {}
	}

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
	}

	/**
	 * Currently active data types
	 *
	 * @var object
	 */
	var active = {
		topic: 0,
		speaker: 0
	}

	/**
	 * Original object property references, for those we need to overrule
	 *
	 * Some smvPlayer properties need overruling as a way to extend them.
	 * This is especially useful for methods that destroy the jwplayer object.
	 *
	 * @var object
	 */
	var orig = {
		// _this.player
		player: {
			next: null,
			previous: null,
			setQualityLevel: null,
			setAudioLanguage: null
		}
	}

	/**
	 * Callback arrays for each event handler
	 *
	 * These are used for re-attachment of callbacks on
	 * destroyed jwplayer objects
	 *
	 * @var object
	 */
	var callbacks = {
		onTime: [],
		onSeek: [],
		onPlay: [],
		onPause: []
	}

	/**
	 * Used to limit seekOnPlays to 1
	 *
	 * Necessary for applySeekOnPlay() to work.
	 *
	 * @var object
	 */
	var seekOnPlay = {}

	/**
	 * Registers a hit of onSeek events, to determine if topics/speakers
	 * need to be disabled when there were no hits.
	 *
	 * @var object
	 */
	var onSeekHit = {
		topic: false,
		speaker: false
	}

	// #@LOW make these ids/class configurable?
	/**
	 * id/class selector strings
	 *
	 * @var object
	 */
	var select = {
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
	}

	/**
	 * Log messages
	 *
	 * @var object
	 */
	var logMsg = {
		no_svpp: 'SVPP not loaded, polling inactive',
		svpp_off: 'Polling disabled',
		no_player_data: 'The player element or player data is not available',
		no_json_support: 'No JSON.parse support in user agent',
		player_data_invalid: 'Player data is invalid or in an unsupported format',
		invalid_player: 'No supported player configured',
		no_jwplayer: 'No jwplayer loaded',
		no_jwplayer_key: 'A jwplayer license key is required',
		no_smvplayer: 'No smvplayer loaded',
		events_topic_init: 'initializing topic event handlers',
		events_speaker_init: 'initializing speaker event handlers',
		events_re: 'Reattached event callbacks',
		no_timestamp: 'Topic has no registered timestamps',
		activate: 'Activated'
	}

	/**
	 * Extend jQuery with .exists(), returns true if length <> 0
	 *
	 * @return boolean
	 */
	$.fn.exists = function() {
		return this.length !== 0;
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

	/**
	 * Initialize live counters, keeping track of the number
	 * of speakers, topics, etc.
	 *
	 * Excludes template elements.
	 *
	 * @return void
	 */
	function initLiveCounters() {
		var $container = $('.' + select.container);
		if (meetingdata.topic) {
			count.topic = $('.topics .topic', $container).not('.template').length;
		}
		if (meetingdata.speaker) {
			count.speaker = $('.speakers .speaker', $container).not('.template').length;
		}
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
					hash = $('#' + select.data).attr('data-hash');

				this.player.onPlay(function() {
					SvpPolling.init(hash, currentPage, interval);
				});
				this.player.onPause(function() {
					SvpPolling.stop();
					deactivateElement('speaker');
					deactivateElement('topic');
				});
			} else {
				log(logMsg.no_svpp, true);
			}
		} else {
			log(logMsg.svpp_off, false);
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
		if (active[type] !== id) {
			activateElement(id, type);
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
		var $template = $('.' + select.container + ' .' + type + 's .' + type).last();
		var $container = $template.parent();

		// remove 'template' classes and $template element out of DOM
		if ($template.hasClass('template')) {
			$template.removeClass('template');
			$template.remove();
			$container.removeClass('template');
		}

		for (var i=0; i < array.length; i++) {
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
				.removeClass('active');
			$container.append($temp);

			count[type].length++;
		}
	}

	/**
	 * Meetingdata refers to streamfile id's, but we can only request playlist id from jwplayer object.
	 * This method pushes playlist id's paired to their streamfile id's into the idMap variable.
	 *
	 * @param data object Playlist data directly parsed from JSON response
	 * @return void
	 */
	function pushPlaylistToIdMap(data) {
		for(var id in data) {
			var p = data[id];
			idMap.playlist[p.streamfileId] = parseInt(id);
		}
	}

	/**
	 * Prepares all eventhandlers on video player, per enabled meetingdata type.
	 *
	 * @return void
	 */
	function initEventHandlers() {
		if (meetingdata.topic) {
			// set jump event on topic clicks
			$('.' + select.container + ' .topics').on('click', '.topic .topic-link', function(e) {
				e.preventDefault();
				_this.jumpToTopic(
					$(this).parent('.topic').attr('data-topic')
				);
			});

			// parse meeting data
			var $topicTimeline = $('#' + select.topicTimeline).first();
			if ($topicTimeline.exists()) {
				var timeline = null;
				try {
					timeline = JSON.parse($topicTimeline.html());
				} catch (e) {
					log(logMsg.no_json_support, true);
					return false;
				}
				if (timeline.length > 0) {
					createTimelineEventHandlers('topic', timeline, true);
				}
				$topicTimeline.html('');
			}

			log(logMsg.events_topic_init, false);
		}

		if (meetingdata.speaker) {
			var $speakerTimeline = $('#' + select.speakerTimeline).first();
			if ($speakerTimeline.exists()) {
				var timeline = null;
				try {
					timeline = JSON.parse($speakerTimeline.html());
				} catch (e) {
					log(logMsg.no_json_support, true);
					return false;
				}
				if (timeline.length > 0) {
					createTimelineEventHandlers('speaker', timeline, false);
				}
				$speakerTimeline.html('');
			}

			log(logMsg.events_speaker_init, false);
		}
	}

	/**
	 * Processes timeline data to create event handler callbacks.
	 *
	 * Although it would seem efficient to only add event handler callbacks of
	 * current playlist-item, this could theoretically work with smvPlayer but
	 * not with jwPlayer. The former destroys the jwPlayer object thus resetting
	 * its event handlers, but the latter offers no such ability without SVPS
	 * doing that, which would only reduce usability and efficiency.
	 *
	 * @param elemType string Either topic or speaker
	 * @param timeline array
	 * @param pushToIdMap boolean On true pushes element id to idMap
	 * @return void
	 */
	function createTimelineEventHandlers(elemType, timeline, pushToIdMap) {
		for (var i=0; i<timeline.length; i++) {
			var time = timeline[i],
				j = i+1;
			time.start = Math.floor(time.relativeTime / 1000);
			time.end = j < timeline.length && time.streamfileId === timeline[j].streamfileId
				? Math.floor(timeline[j].relativeTime / 1000)
				: Number.MAX_SAFE_INTEGER;

			if (pushToIdMap) {
				idMap[elemType][time.id] = {
					playlist: idMap.playlist[time.streamfileId],
					time: time.start
				}
			}

			// set events on times
			_this.player.onTime((function(t, type) {
				return function(e) {
					if (e.position >= t.start && e.position < (t.start+0.4)
						&& t.id !== active[type]
						&& timeIsOnPlaylist(t)
					) {
						activateElement(t.id, type);
					}
				};
			})(time, elemType));

			// set events on seeks
			_this.player.onSeek((function(t, type) {
				return function(e) {
					if (e.offset >= t.start && e.offset < t.end
						&& timeIsOnPlaylist(t)
					) {
						if (t.id !== active[type]) {
							activateElement(t.id, type);
						}
						// even if t.id === active.type, onSeekHit needs to be marked to prevent deactivation
						onSeekHit[type] = true;
					}
				}
			})(time, elemType));
		}

		// final onseek deactivates actives if no previous onseek had a hit
		_this.player.onSeek((function(type) {
			return function(e) {
				if (!onSeekHit[type] && active[type] !== 0) {
					deactivateElement(type);
				}
				onSeekHit[type] = false;
			}
		})(elemType));
	}

	/**
	 * Resets _this.jw (jwplayer object) to the current instance.
	 * Needs to be called when the jwplayer instance was removed
	 * and a new instance created, as smvplayer does.
	 *
	 * @return void
	 */
	function reset() {
		_this.jw = jwplayer(select.player);
		// if really new, an onReady will be fired
		_this.jw.onReady(function(e) {
			deactivateElement('speaker');
			deactivateElement('topic');
			// all event handler callbacks need to be re-attached
			reattachEventCallbacks();
		});
	}

	/**
	 * Reattach callbacks on event handlers, if stored in respective arrays
	 *
	 * @return void
	 */
	function reattachEventCallbacks() {
		for (var c in callbacks.onTime) {
			this.jw.onTime(callbacks.onTime[c]);
		}
		for (var c in callbacks.onSeek) {
			this.jw.onSeek(callbacks.onSeek[c]);
		}
		for (var c in callbacks.onPlay) {
			this.jw.onPlay(callbacks.onPlay[c]);
		}
		for (var c in callbacks.onPause) {
			this.jw.onPause(callbacks.onPause[c]);
		}
		log(logMsg.events_re, false);
	}

	/**
	 * Deactivate all elements of a type
	 *
	 * Assumes template structure .plugincontainer .elems .elem.active
	 *
	 * @param type string speaker/topic
	 * @return void
	 */
	function deactivateElement(type) {
		if (meetingdata[type]) {
			$('.' + select.container + ' .' + type + 's .' + type + '.active').removeClass('active');
			active[type] = 0;
		}
	}

	/**
	 * Activate element type
	 *
	 * @param id int Element id
	 * @param type string topic/speaker
	 * @return void
	 */
	function activateElement(id, type) {
		deactivateElement(type);
		$('.' + select.container + ' .' + type + 's .' + type + '[data-' + type + '=' + id + ']').first().addClass('active');
		active[type] = id;
		log(logMsg.activate + ' ' + type + ' ' + id, false);
	}

	/**
	 * Recursively call playlistNext()
	 *
	 * smvplayer only, jwplayer doesn't need this.
	 *
	 * @param current int Current recursion in moving next
	 * @param limit int Endpoint of recursion
	 * @return void
	 */
	function recursiveMoveNext(current, limit) {
		_this.player.playlistNext();
		current++;
		if (current < limit) {
			_this.jw.onReady(function (e) {
				// timeout prevents flash from crashing :')
				setTimeout(function() {
					recursiveMoveNext(current, limit);
				}, 50);
			});
		}
	}

	/**
	 * Recursively call playlistPrev()
	 *
	 * smvplayer only, jwplayer doesn't need this.
	 *
	 * @param current int Current recursion in moving back
	 * @param limit int Endpoint of recursion
	 * @return void
	 */
	function recursiveMovePrevious(current, limit) {
		_this.player.playlistPrev();
		current--;
		if (current > limit) {
			_this.jw.onReady(function (e) {
				// timeout prevents flash from crashing
				setTimeout(function() {
					recursiveMovePrevious(current, limit);
				}, 50);
			});
		}
	}

	/**
	 * Applies a seek() on the player's onPlay event handler.
	 * Useful when player isn't playing, otherwise flash may crash :')
	 *
	 * Justifies the seekOnPlay var, which is a shame but there's no way
	 * around it for now.
	 *
	 * @param o object Topic object
	 * @return void
	 */
	function applySeekOnPlay(o) {
		if (!seekOnPlay.hasOwnProperty(o.playlist)) {
			seekOnPlay[o.playlist] = {};
		}
		seekOnPlay[o.playlist][o.time] = true;

		// note that this onPlay isn't added to callbacks with smvPlayer, that would be incredibly useless
		_this.jw.onPlay(function (e) {
			// there's no way to delete onPlay event callbacks.. so we need these conditions :(
			if (seekOnPlay[o.playlist][o.time]) {
				_this.player.seek(o.time);
				seekOnPlay[o.playlist][o.time] = false;
			}
		});
	}

	/**
	 * Returns if time is on current playlist item
	 *
	 * @param t object Timestamp object
	 * @return boolean
	 */
	function timeIsOnPlaylist(t) {
		return idMap.playlist[t.streamfileId] === _this.player.getPlaylistIndex();
	}

	/**
	 * Initialize a jwplayer object with its licensekey if configured via TS.
	 * Check if jwplayer exists first.
	 *
	 * @param requireLicense boolean If true, will fail if no licenseKey was provided
	 * @return booelean True on success, false on failure
	 */
	function initJwPlayer(requireLicense) {
		var licenseKey = '###JWPLAYER_KEY###';

		if (typeof(jwplayer) === 'undefined') {
			log(logMsg.no_jwplayer, true);
			return false;
		}
		if (licenseKey) {
			jwplayer.key = licenseKey;
		} else if(requireLicense) {
			log(logMsg.no_jwplayer_key, true);
			return false;
		}
		return true;
	}

	/**
	 * Create the video player by using the smvplayer object
	 *
	 * @param data object Parsed JSON data
	 * @return boolean True on success, false on failure
	 */
	function createSmvPlayer(data) {
		if (initJwPlayer(true)) {
			// smvplayer object needs to exist
			if (typeof(smvplayer) !== 'undefined') {
				_this.player = smvplayer(select.player);
				_this.player.init(data);
				// smvplayer does not provide full jwplayer api, so we need a reference to preserve consistency in all of SVPS
				_this.jw = jwplayer(select.player);

				// Smvplayer calls jwplayer.remove() on moving in the playlist, which clears the entire jwplayer
				// instance, including event handlers, so we need a construct that reassigns this.jw and
				// all of the event handler callbacks. This is what reset() is for.
				_this.player.onTime = function(callback) {
					callbacks.onTime.push(callback);
					_this.jw.onTime(callback);
				}
				_this.player.onSeek = function(callback) {
					callbacks.onSeek.push(callback);
					_this.jw.onSeek(callback);
				}
				_this.player.onPlay = function(callback) {
					callbacks.onPlay.push(callback);
					_this.jw.onPlay(callback);
				}
				_this.player.onPause = function(callback) {
					callbacks.onPause.push(callback);
					_this.jw.onPause(callback);
				}
				// overrule player.next()
				orig.player.next = _this.player.next;
				_this.player.next = function() {
					orig.player.next();
					reset();
				}
				// overrule player.previous()
				orig.player.previous = _this.player.previous;
				_this.player.previous = function() {
					orig.player.previous();
					reset();
				}
				// overrule player.setQualityLevel()
				orig.player.setQualityLevel = _this.player.setQualityLevel;
				_this.player.setQualityLevel = function(q) {
					orig.player.setQualityLevel(q);
					reset();
				}
				// overrule player.setAudioLanguage()
				orig.player.setAudioLanguage = _this.player.setAudioLanguage;
				_this.player.setAudioLanguage = function(l) {
					orig.player.setAudioLanguage(l);
					reset();
				}
				// because smvplayer doesnt use the playlist as jwplayer does, we emulate
				// some specific playlist methods on this.player to create a shared api
				// where this is covenient for SVPS
				_this.player.getPlaylistIndex = function() {
					return _this.player.getTimeline().currentItem;
				}
				_this.player.playlistNext = function() {
					_this.player.next();
				}
				_this.player.playlistPrev = function() {
					_this.player.previous();
				}
				_this.player.playlistItem = function(index) {
					moveAction = index - _this.player.getPlaylistIndex();
					if (moveAction > 0) {
						recursiveMoveNext(0, moveAction);
					} else if(moveAction < 0) {
						recursiveMovePrevious(0, moveAction);
					}
				}

				return true;
			}

			log(logMsg.no_smvplayer, true);
		}
		return false;
	}

	/**
	 * Create the video player by using the jwplayer object
	 *
	 * @param data object Parsed JSON data
	 * @return boolean True on success, false on failure
	 */
	function createJwPlayer(data) {
		if (initJwPlayer(false)) {
			jwplayer(select.player).setup(data);
			// apparently setup() creates a new object, so assign these AFTER setup()
			_this.player = jwplayer(select.player);
			_this.jw = _this.player;

			// on changing playlist item, deactivate elements
			_this.player.onPlaylistItem(function(e) {
				deactivateElement('speaker');
				deactivateElement('topic');
			});
			return true;
		}
		return false;
	}

	/**
	 * Create the video player by using native HTML5 methods
	 *
	 * UNFINISHED IMPLEMENTATION
	 *
	 * @return boolean True on success, false on failure
	 */
	function createNativeHtml5Player() {
		// #@LOW finish implementation?
		if (!!document.createElement('video').canPlayType) {
			// fallback
		}
		log('ERROR: unfinished implementation', true);
		return false;
	}

	/**
	 * Actual SVPS object, offers public methods/properties
	 *
	 * @var object
	 */
	var _this = {

		/**
		 * Wrapper to substitute the actual player object
		 * Keep public for debugging
		 *
		 * @var object
		 */
		player: null,

		/**
		 * Wrapper to substitute jwplayer object for jwplayer-only calls
		 * Keep public for debugging
		 *
		 * @var object
		 */
		jw: null,

		/**
		 * Consistent property determining if current item is live or not (vod)
		 * Keep public for debugging
		 *
		 * @var boolean
		 */
		isLiveStream: false,

		/**
		 * Initializes the video-player per configuration.
		 * This is the SVPS startingpoint and must be called once
		 * for the videoplayer to work as intended.
		 *
		 * @return boolean True on success, false on failure
		 */
		init: function() {
			// check if necessary elements exist
			var $player = $('#' + select.player),
				$data = $('#' + select.data).first();
			if (!$player.exists() || !$data.exists()) {
				// at least one of necessary elements does not exist
				log(logMsg.no_player_data, true);
				return false;
			}

			// read & parse data
			var data = null;
			try {
				data = JSON.parse($data.html().trim());
			} catch (e) {
				log(logMsg.no_json_support, true);
				return false;
			}
			$data.html('');
			if (typeof(data) !== 'object') {
				log(logMsg.player_data_invalid, true)
				return false;
			}

			// Supports multiple player types
			switch (playerType) {
				case 2:
					if (!createSmvPlayer(data)) {
						return false;
					}
					break;
				case 1:
					if (!createJwPlayer(data)) {
						return false
					}
					break;
				default:
					// no valid player configuration
					log(logMsg.invalid_player, true);
					return false;
			}

			// meetingdata refers to streamfile id's, but we can only request playlist id from jwplayer object
			if (data.hasOwnProperty('playlist')) {
				pushPlaylistToIdMap(data.playlist);
			}
			// determine if this is a livestream
			if (data.hasOwnProperty('application')) {
				this.isLiveStream = data.application === 'rtplive';
			}

			if (this.isLiveStream) {
				// note that event handlers are note initialized during a livestream:
				// this is because a livestream is fed via polling and not via interaction
				if (meetingdata.topic || meetingdata.speaker) {
					initLiveCounters();
					initPolling();
				}
			} else {
				initEventHandlers();
			}
			return true;
		},

		/**
		 * Process a meetingdata-change as retrieved by polling.
		 *
		 * Note that this method assumes meetingdata is only added,
		 * never removed!
		 *
		 * @param data object Parsed JSON data
		 * @return void
		 */
		processMeetingdataChange: function(data) {
			// note that empty/null elements are translated by json-encoding to
			// value "false" in some cases, hence these extra checks

			if (meetingdata.topic) {
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
				// activate latest topic timestamp?
				if (data.hasOwnProperty('topicTimeline')
					&& data.topicTimeline !== false
					&& data.topicTimeline.length > 0
				) {
					activateLatestElement(data.topicTimeline, 'topic');
				}
			}
			if (meetingdata.speaker) {
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
				// activate latest speaker timestamp?
				if (data.hasOwnProperty('speakerTimeline')
					&& data.speakerTimeline !== false
					&& data.speakerTimeline.length > 0
				) {
					activateLatestElement(data.speakerTimeline, 'speaker');
				}
			}
		},

		/**
		 * Jump to the correct playlist and topic
		 *
		 * @param id int Topic ID
		 * @return void
		 */
		jumpToTopic: function(id) {
			var topic = idMap.topic[id];
			if (topic !== undefined) {
				if (topic.playlist !== this.player.getPlaylistIndex()) {
					this.player.playlistItem(topic.playlist);
				}
				// e.g. when IDLE (smv) or BUFFERING (jw)
				var state = this.jw.getState();
				if (state !== 'PLAYING') {
					// not all relevant onSeek events will trigger if player hasn't started
					applySeekOnPlay(topic);
					this.jw.play(true);
				} else {
					this.player.seek(topic.time);
				}
			} else {
				log(logMsg.no_timestamp, true);
			}
		}
	}

	return _this;
})(jQuery);