/**
 * Streamovations Video Player Starter (SVPS)
 *
 * Triggers several jQuery events you can use to interact with:
 * - SVPS:play = playing starts
 * - SVPS:inactive-topic = all topics deactivated
 * - SVPS:inactive-speaker = all speakers deactivated
 * - SVPS:active-topic {id,index} = topic activates, provides data
 * - SVPS:active-speaker {id,index} = speaker activates, provides data
 *
 * @package streamovations_vp
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
var SvpStarter = (function($) {
	'use strict';

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
	var pollingInterval = parseInt('###POLLING_INTERVAL###', 10);

	/**
	 * Current TYPO3 page ID, provided via TS
	 *
	 * @var int
	 */
	var currentPage = parseInt('###CURRENT_PAGE_ID###', 10);

	/**
	 * Player type, configured via TS
	 *
	 * @var int
	 */
	var playerType = parseInt('###PLAYER_TYPE###', 10);

	/**
	 * Determines if meetingdata-type is enabled, configured via TS
	 *
	 * @var object
	 */
	var meetingdata = {
		breaks: parseInt('###MEETINGDATA_BREAKS###', 10),
		topic: parseInt('###MEETINGDATA_TOPICS###', 10),
		speaker: parseInt('###MEETINGDATA_SPEAKERS###', 10)
	};

	/**
	 * Maps id's of objects to value or object
	 *
	 * @var object
	 */
	var idMap = {
		// topic id: topic obj
		topic: {}
		//speaker: {}
	};

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

	/**
	 * Currently active data types / player
	 *
	 * @var object
	 */
	var active = {
		topic: 0,
		speaker: 0,
		eventBreak: false,
		pausePlayInterval: null
	};

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
	};

	/**
	 * Used to limit seekOnPlays to 1
	 *
	 * Necessary for applySeekOnPlay() to work on anything other than JW7.
	 *
	 * @var object
	 */
	var seekOnPlay = {};

	/**
	 * Used to limit seekOnPlays to 1
	 *
	 * Necessary for JW6 onPlaylist seeks.
	 *
	 * @var object
	 */
	var seekOnPlaylist = {};

	/**
	 * Registers a hit of onSeek events, to determine if topics/speakers
	 * need to be disabled when there were no hits.
	 *
	 * @var object
	 */
	var onSeekHit = {
		topic: false,
		speaker: false
	};

	/**
	 * Contains all eventhandler closures for use by the player that
	 * are called multiple times in a single request.
	 *
	 * @var object
	 */
	var eventHandler = {

		/**
		 * For use of general detection of current type element
		 *
		 * onTime()
		 *
		 * @param t object Timestamp
		 * @param type string topic/speaker
		 * @return {Function}
		 */
		onTime: function(t, type) {
			return function(e) {
				if (e.position >= t.start && e.position < (t.start+0.4)
					&& t.id !== active[type]
					&& t.playlist === getPlaylistIndex()
				) {
					activateElement(t.id, type);
				}
			};
		},

		/**
		 * For use of general detection of type element of seeked time
		 *
		 * onSeek()
		 *
		 * @param t object Timestamp
		 * @param type string topic/speaker
		 * @return {Function}
		 */
		onSeek: function(t, type) {
			return function(e) {
				if (e.offset >= t.start && e.offset < t.end
					&& t.playlist === getPlaylistIndex()
				) {
					if (t.id !== active[type]) {
						activateElement(t.id, type);
					}
					// even if t.id === active.type, onSeekHit needs to be marked to prevent deactivation
					onSeekHit[type] = true;
				}
			};
		},

		/**
		 * For use of finalizing the onSeek() handler callbacks as a means to
		 * deactivate the given type when no matches occurred in previous callbacks
		 *
		 * onSeek()
		 *
		 * @param type string topic/speaker
		 * @return {Function}
		 */
		onSeekFinal: function(type) {
			return function(e) {
				if (!onSeekHit[type] && active[type] !== 0) {
					deactivateElement(type);
				}
				onSeekHit[type] = false;
			};
		}

	};

	/**
	 * id/class selector strings
	 *
	 * @var object
	 */
	var select = {
		// id of player HTML element
		player: 'tx-streamovations-vp-play',
		playerObj: 'tx-streamovations-vp-play',
		playerPI: 'tx-streamovations-vp-play',
		// class of player container
		playerContainer: 'video-player-container',
		// player engine wrapper pre-wrap id
		smvWrapper1: 'smvplayer_',
		smvWrapper2: 'engineWrapper_',
		hlsWrapper: 'smv_html5videotag_',
		// player engine wrapper post-wrap id
		html5Wrapper: '_html5videotag',
		// id of player data HTML element
		data: 'tx-streamovations-vp-playerdata',
		config: 'tx-streamovations-vp-playerconfig',
		// id of topic timeline HTML element
		topicTimeline: 'tx-streamovations-vp-topictimeline',
		// id of speaker timeline HTML element
		speakerTimeline: 'tx-streamovations-vp-speakertimeline',
		// container class of module
		container: 'tx-streamovations-vp'
	};

	/**
	 * Log messages
	 *
	 * @var object
	 */
	var logMsg = {
		no_svpp: 'SVPP not loaded, polling inactive',
		svpp_off: 'Polling disabled',
		no_player_data: 'The player element or player data is not available',
		no_playlist: 'Missing essential playlist data',
		no_json_support: 'No JSON.parse support in user agent',
		player_data_invalid: 'Player data is invalid or in an unsupported format',
		invalid_player: 'No supported player configured',
		invalid_eventbreak: 'Eventbreak data is invalid or in an unsupported format',
		no_jwplayer: 'No jwplayer loaded',
		no_jwplayer_key: 'A jwplayer license key is required',
		no_smvplayer: 'No smvplayer loaded',
		events_topic_init: 'initializing topic event handlers',
		events_speaker_init: 'initializing speaker event handlers',
		events_re: 'Reattached event callbacks',
		no_timestamp: 'Topic has no registered timestamps',
		no_playlist_seek: 'Can only seek to other playlist item during playback',
		activate: 'Activated',
		video_break_title: '###VIDEO_BREAK_TITLE###',
		video_break_sub: '###VIDEO_BREAK_SUB###'
	};

	/**
	 * Extend jQuery with .exists(), returns true if length <> 0
	 *
	 * @return boolean
	 */
	$.fn.exists = function() {
		return this.length !== 0;
	};

	/**
	 * onSeek wrapper. Method will be created on player initialization.
	 *
	 * @param callback object Function callback
	 * @return void
	 */
	var onSeek = null;

	/**
	 * onTime wrapper. Method will be created on player initialization.
	 *
	 * @param callback object Function callback
	 * @return void
	 */
	var onTime = null;

	/**
	 * onPlay wrapper. Method will be created on player initialization.
	 *
	 * @param callback object Function callback
	 * @return void
	 */
	var onPlay = null;

	/**
	 * onPause wrapper. Method will be created on player initialization.
	 *
	 * @param callback object Function callback
	 * @return void
	 */
	var onPause = null;

	/**
	 * Get current playlist id wrapper. Method will be created on player initialization.
	 *
	 * @return integer
	 */
	var getPlaylistIndex = null;

	/**
	 * Get playlist id from time object wrapper. Method will be created on player initialization.
	 *
	 * @return integer
	 */
	var getPlaylistIndexFromTimeObject = null;

	/**
	 * Seek topic wrapper. Method will be created on player initialization.
	 *
	 * @param topic object Topic to seek
	 * @return void
	 */
	var seek = null;

	/**
	 * Seek time wrapper. Method will be created on player initialization.
	 *
	 * @param time integer
	 * @param playlist integer
	 * @return void
	 */
	var seekTime = null;

	/**
	 * Player stop wrapper. Method will be created on player initialization.
	 *
	 * @return void
	 */
	var stop = null;

	/**
	 * Play on Ready wrapper. Method will be created on player initialization.
	 *
	 * @param callback object Function callback
	 * @return void
	 */
	var playOnReady = null;

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
	 * @param continuousPolling boolean
	 * @return void
	 */
	function initPolling(continuousPolling) {
		if (pollingInterval > 0) {
			if (typeof SvpPolling !== 'undefined') {
				var interval = pollingInterval * 1000,
				// @TODO specificity!
					hash = $('#' + select.data).attr('data-hash');

				if (continuousPolling) {
					SvpPolling.init(hash, currentPage, interval);
				} else {
					onPlay(function() {
						SvpPolling.init(hash, currentPage, interval);
					});
					onPause(function() {
						SvpPolling.stop();
						deactivateElement('speaker');
						deactivateElement('topic');
					});
				}
			} else {
				log(logMsg.no_svpp, true);
			}
		} else {
			log(logMsg.svpp_off, false);
		}
	}

	/**
	 * Processes the latest event break that is passed along
	 *
	 * @param array array Contains eventbreaks
	 * @return void
	 */
	function processLatestEventBreak(array) {
		var eventBreak = array[array.length-1];
		try {
			if (eventBreak.valid) {
				var now = Math.round(new Date().getTime() / 1000);
				if (active.eventBreak) {
					if (eventBreak.end !== null && eventBreak.utcEnd <= now) {
						resumeStream();
					}
				} else if (eventBreak.utc <= now) {
					if (eventBreak.end === null || eventBreak.utcEnd > now) {
						interruptStream();
					}
				}
			}
		} catch (e) {
			log(logMsg.invalid_eventbreak, true);
			// ideally we would disable breaks, but this is open to abuse because
			// SVPS.processmeetingdatachange() is a public method
			//meetingdata.breaks = false;
		}
	}

	/**
	 * Resumes an interrupted videostream
	 *
	 * @return void
	 */
	function resumeStream() {
		// stop any active polling
		SvpPolling.stop();

		var $break = $('.' + select.container + ' .video-player-break'),
			newPlayer = '<div id="' + select.player + '" class="video-player"></div>';

		// replace the interruption with a new player element
		$break.parent().append(newPlayer);
		$break.remove();

		active.eventBreak = false;
		// initialize the new player element from scratch
		SVPS.init();
		// automatically start playing
		playOnReady();
	}

	/**
	 * Interrupts a videostream
	 *
	 * @return void
	 */
	function interruptStream() {
		// stop playing/polling/everything and reset any previously set callback
		stop();
		callbacks = {
			onTime: [],
			onSeek: [],
			onPlay: [],
			onPause: []
		};

		var $player = $('.' + select.container + ' #' + select.playerPI).first(),
			replacement = '<div class="video-player-break"><div class="text"><div class="title">' + logMsg.video_break_title + '</div><div class="sub">' + logMsg.video_break_sub + '</div></div></div>';

		// replace the player object with an interruption
		$player.parent().append(replacement);
		$player.remove();

		active.eventBreak = true;

		// start continuous polling
		initPolling(true);
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
	 * This returns a playlist streamfileId => playlistIndex collection.
	 *
	 * @param data object Playlist data directly parsed from JSON response
	 * @return object
	 */
	function formatPlaylistData(data) {
		var playlistData = {};
		for(var id in data) {
			if (data.hasOwnProperty(id)) {
				var p = data[id];
				playlistData[p.streamfileId] = parseInt(id, 10);
			}
		}
		return playlistData;
	}

	/**
	 * Prepares all eventhandlers on video player, per enabled meetingdata type.
	 *
	 * @return void
	 */
	function initEventHandlers() {
		var timeline = null;
		if (meetingdata.topic) {
			// set jump event on topic clicks
			var $topics = $('.' + select.container + ' .topics');
			$topics.on('click', '.topic .topic-link', function(e) {
				e.preventDefault();
				SVPS.jumpToTopic(
					$(this).parent('.topic').attr('data-topic')
				);
			});

			// @TODO specificity!
			// parse meeting data
			var $topicTimeline = $('#' + select.topicTimeline).first();
			if ($topicTimeline.exists()) {
				try {
					timeline = JSON.parse($topicTimeline.html());
				} catch (e1) {
					log(logMsg.no_json_support, true);
					return false;
				}
				if (timeline.length > 0) {
					createTimelineEventHandlers('topic', timeline, true);
				}
				$topicTimeline.html('');
			}

			// remove the anchor tag from topics without a time
			$topics.find('.topic').each(function (i, topic) {
				var $topic = $(topic);
				if (idMap['topic'][$topic.attr('data-topic')] === undefined) {
					var $topicLink = $topic.find('.topic-link');
					if ($topicLink[0]) {
						var text = $topicLink.text();
						$topic.append('<span class="topic-title">' + text + '</span>');
						$topicLink.remove();
					}
				}
			});

			log(logMsg.events_topic_init, false);
		}

		if (meetingdata.speaker) {
			// @TODO specificity!
			var $speakerTimeline = $('#' + select.speakerTimeline).first();
			if ($speakerTimeline.exists()) {
				timeline = null;
				try {
					timeline = JSON.parse($speakerTimeline.html());
				} catch (e2) {
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
			time.playlist = getPlaylistIndexFromTimeObject(time);

			if (pushToIdMap) {
				idMap[elemType][time.id] = {
					playlist: time.playlist,
					time: time.start
				};
			}

			// set events on times
			onTime(eventHandler.onTime(time, elemType));

			// set events on seeks
			onSeek(eventHandler.onSeek(time, elemType));
		}

		// final onseek deactivates actives if no previous onseek had a hit
		onSeek(eventHandler.onSeekFinal(elemType));
	}

	/**
	 * Initializes HTML5 onTime vent listener.
	 *
	 * @return void
	 */
	function initOnTime() {
		// add onTime alternative event listener
		SVPS.player.addEventListener('timeupdate', function(e) {
			for (var c in callbacks.onTime) {
				if (callbacks.onTime.hasOwnProperty(c)) {
					callbacks.onTime[c]({position: this.currentTime});
				}
			}
		});
	}

	/**
	 * Initializes HTML5 event listeners for onTime and onSeek alternatives.
	 *
	 * @return void
	 */
	function initHtml5EventListeners() {
		initOnTime();

		// add onSeek alternative event listener
		SVPS.player.addEventListener('seeking', function(e) {
			log("Seeking event fired: time " + this.currentTime);
			for (var c in callbacks.onSeek) {
				if (callbacks.onSeek.hasOwnProperty(c)) {
					callbacks.onSeek[c]({offset: this.currentTime});
				}
			}
		});
	}

	/**
	 * Resets SVPS.player events (html5 tag) to the correct time and playlist.
	 * Needs to be called when the smvplayer replaces the html5 tag and
	 * effectively destroys a number of parameters.
	 *
	 * @return void
	 */
	function resetHtml5(time, playlist) {
		deactivateElement('speaker');
		deactivateElement('topic');
		applySeekOnPlay(time, playlist);
		// only play-specific callbacks need to re-attached
		reattachPlayCallbacks();
	}

	/**
	 * Resets SVPS.player events (hls tag) to the correct time and playlist.
	 * Needs to be called when the smvplayer replaces the html5 tag and
	 * effectively destroys a number of parameters.
	 *
	 * @return void
	 */
	function resetHls(time, playlist) {
		deactivateElement('speaker');
		deactivateElement('topic');
		SVPS.player = document.getElementById(select.playerObj);

		if (time !== undefined) {
			applySeekOnPlay(time, playlist);
		}

		initHtml5EventListeners();
	}

	/**
	 * Resets SVPS.player events (hls tag) to the correct time and playlist.
	 * Needs to be called when the smvplayer replaces the html5 tag and
	 * effectively destroys a number of parameters.
	 *
	 * @return void
	 */
	function resetMe(time, playlist) {
		deactivateElement('speaker');
		deactivateElement('topic');
		SVPS.player = document.getElementById(select.playerObj);

		if (time !== undefined) {
			applySeekOnPlay(time, playlist);
		}
	}

	/**
	 * Resets SVPS.jw (jwplayer object) to the current instance.
	 * Needs to be called when the jwplayer instance was removed
	 * and a new instance created, as smvplayer does.
	 *
	 * @return void
	 */
	function resetJw() {
		SVPS.jw = jwplayer(select.playerObj);
		// if really new, an onReady will be fired
		SVPS.jw.onReady(function(e) {
			deactivateElement('speaker');
			deactivateElement('topic');
			// all event handler callbacks need to be re-attached
			reattachEventCallbacks();
		});
	}

	/**
	 * Executes the onTime callbacks on demand. Useful if these
	 * can't be set to an event listener, but you want to run
	 * them with an interval.
	 *
	 * @return void
	 */
	function executeOnTimeCallbacks() {
		for (var c in callbacks.onTime) {
			if (callbacks.onTime.hasOwnProperty(c)) {
				// note the parenthesis, necessary to execute
				callbacks.onTime[c]();
			}
		}
	}

	/**
	 * Reattach callbacks on event handlers, if stored in respective arrays.
	 * Necessary for smvplayer's resetJw() call, see that method's description.
	 *
	 * @return void
	 */
	function reattachEventCallbacks() {
		var c = null;
		for (c in callbacks.onTime) {
			if (callbacks.onTime.hasOwnProperty(c)) {
				SVPS.jw.onTime(callbacks.onTime[c]);
			}
		}
		for (c in callbacks.onSeek) {
			if (callbacks.onSeek.hasOwnProperty(c)) {
				SVPS.smv.onSeek(callbacks.onSeek[c]);
			}
		}
		reattachPlayCallbacks();
	}

	/**
	 * Reattach callbacks on event handlers, if stored in respective arrays.
	 * Necessary for smvplayer's resetJw() and resetHtml5() calls, see
	 * those methods' descriptions.
	 *
	 * @return void
	 */
	function reattachPlayCallbacks() {
		var c = null;
		for (c in callbacks.onPlay) {
			if (callbacks.onPlay.hasOwnProperty(c)) {
				SVPS.smv.onPlay(callbacks.onPlay[c]);
			}
		}
		for (c in callbacks.onPause) {
			if (callbacks.onPause.hasOwnProperty(c)) {
				SVPS.smv.onPause(callbacks.onPause[c]);
			}
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
			var $elems = $('.' + select.container + ' .' + type + 's');

			$('.' + type + '.active', $elems).removeClass('active');
			active[type] = 0;
			$elems.trigger('SVPS:inactive-' + type);
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
		var $elems = $('.' + select.container + ' .' + type + 's'),
			$elem = $('.' + type + '[data-' + type + '=' + id + ']', $elems);

		// avoiding deactivateElement() because don't want its trigger and we already have $elems
		$('.' + type + '.active', $elems).removeClass('active');
		active[type] = id;

		$elem.first().addClass('active');
		$elems.trigger('SVPS:active-' + type, {
			id: id,
			index: $elem.index()
		});
		log(logMsg.activate + ' ' + type + ' ' + id, false);
	}

	/**
	 * Applies a seek() on the player's onPlay event handler.
	 * Useful when player isn't playing, otherwise flash may crash
	 * or the player may stall :')
	 *
	 * Justifies the seekOnPlay var, which is a shame but there's no way
	 * around it for now except with JW player 7. (which replaces this
	 * method on initialization)
	 *
	 * @param time integer
	 * @param playlist integer
	 * @return void
	 */
	function applySeekOnPlay(time, playlist) {
		if (!seekOnPlay.hasOwnProperty(playlist)) {
			seekOnPlay[playlist] = {};
		}
		seekOnPlay[playlist][time] = true;
		onPlay(function (e) {
			// can't delete the onPlay event callback.. hence the condition :(
			if (seekOnPlay[playlist][time]) {
				seekTime(time, playlist);
				seekOnPlay[playlist][time] = false;
			}
		});
	}

	/**
	 * Initialize a jwplayer object with its licensekey if configured via TS.
	 * Check if jwplayer exists first.
	 *
	 * @param requireLicense boolean If true, will fail if no licenseKey was provided
	 * @return booelean True on success, false on failure
	 */
	function initJwPlayerVariables(requireLicense) {
		// @FIX ___________change how this works
		var licenseKey = '###JWPLAYER_KEY###';

		if (typeof jwplayer === 'undefined') {
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
	 * @see http://wiki.streamovations.be/doku.php?id=smvplayer:javascript-api
	 */
	function initSmvPlayer(data, config) {
		// smvplayer object needs to exist
		if (typeof smvplayer !== 'undefined') {
			SVPS.smv = smvplayer(select.player);
			try {
				SVPS.smv.init(data, config);
			} catch (e) {
				log(e, true);
			}

			// works for any type of smvplayer
			getPlaylistIndex = function() {
				return SVPS.smv.getCurrentPlaylistItem().streamfileId;
			};
			getPlaylistIndexFromTimeObject = function(time) {
				return time.streamfileId;
			}
			// @LOW SMVnative: we can't set it to autoplay if the video element wasn't created with an autoplay attribute
			playOnReady = function() {
				SVPS.smv.whenReady(function() {
					SVPS.smv.play();
				});
			}
			stop = function() {
				SVPS.smv.stop();
			}

			// do further initialization based on the utilized engine
			var engine = SVPS.smv.getEngine();
			if (engine === 'hlsjs' || engine === 'html5') {
				initSmvHlsPlayer();
			} else if (engine === 'me') {
				initSmvMePlayer();
			} else if (engine === 'jw') {
				initSmvJwPlayer();
			}/* else if (engine === 'html5') {
				initSmvHtml5Player();
			}*/

			// post init class of original player element changes with SMV
			select.playerPI = select.smvWrapper1 + select.player;
			return true;
		}

		log(logMsg.no_smvplayer, true);
		return false;
	}

	/**
	 * SMV player initialization that is JW-player engine specific.
	 *
	 * @return void
	 */
	function initSmvJwPlayer() {
		// reflect active player object
		SVPS.player = SVPS.smv;
		// SMV player changes the player id, so we should too if we want to get the actual JW player object
		select.playerObj = select.smvWrapper1 + select.smvWrapper2 + select.player;
		// smvplayer does not provide full jwplayer api, so we need a reference to preserve consistency
		SVPS.jw = jwplayer(select.playerObj);

		// Smvplayer calls jwplayer.remove() on moving in the playlist, which clears the entire jwplayer
		// instance, including event handlers, so we need a construct that reassigns SVPS.jw and
		// all of the event handler callbacks. This is what resetJw() is for.
		onTime = function(callback) {
			callbacks.onTime.push(callback);
			SVPS.jw.onTime(callback);
		};
		// @LOW note that according to Streamovations employees, it could be a bug that these aren't reattached by SMV player
		onSeek = function(callback) {
			callbacks.onSeek.push(callback);
			SVPS.smv.onSeek(callback);
		};
		onPlay = function(callback) {
			callbacks.onPlay.push(callback);
			SVPS.smv.onPlay(callback);
		};
		onPause = function(callback) {
			callbacks.onPause.push(callback);
			SVPS.smv.onPause(callback);
		};

		// Original smv property references, for those we need to overrule
		var orig = {};
		// overrule player.next()
		orig.next = SVPS.smv.next;
		SVPS.smv.next = function() {
			orig.next();
			resetJw();
		};
		// overrule player.previous()
		orig.previous = SVPS.smv.previous;
		SVPS.smv.previous = function() {
			orig.previous();
			resetJw();
		};
		// overrule player.setQualityLevel()
		orig.setQualityLevel = SVPS.smv.setQualityLevel;
		SVPS.smv.setQualityLevel = function(q) {
			orig.setQualityLevel(q);
			resetJw();
		};
		// overrule player.setAudioLanguage()
		orig.setAudioLanguage = SVPS.smv.setAudioLanguage;
		SVPS.smv.setAudioLanguage = function(l) {
			orig.setAudioLanguage(l);
			resetJw();
		};

		// seek methods
		seek = function(topic) {
			// e.g. when BUFFERING or PAUSED (seek on IDLE doesn't stall SMV)
			var state = SVPS.smv.getStatus();
			if (state !== 'PLAYING' && state !== 'IDLE') {
				// not all relevant onSeek events will trigger if player hasn't started
				applySeekOnPlay(topic.time, topic.playlist);
				SVPS.smv.play();
			} else {
				seekTime(topic.time, topic.playlist);
			}
		}
		seekTime = function(time, playlist) {
			if (playlist === getPlaylistIndex()) {
				// otherwise, smv player issues a warning
				playlist = null;
			}
			SVPS.smv.seek(time, playlist);
			if (playlist !== null) {
				resetJw();
			}
		}

	}

	/**
	 * SMV player initialization that is HLS/JS engine specific.
	 *
	 * @return void
	 */
	function initSmvHlsPlayer() {
		// SMV player changes the player id, so we should too if we want to get the actual HTML5 video tag
		select.playerObj = select.hlsWrapper + select.player;
		// reflect active player object
		SVPS.player = document.getElementById(select.playerObj);

		SVPS.smv.onReload(function(e) {
			resetHls();
		});

		initHtml5EventListeners();

		// our own event listeners aren't destroyed, so we can keep these simple
		onTime = function(callback) {
			callbacks.onTime.push(callback);
		};
		onSeek = function(callback) {
			callbacks.onSeek.push(callback);
		};
		// onPlay and onPause survive a playlist change, but not Quality or Audio change
		onPlay = function(callback) {
			//callbacks.onPlay.push(callback);
			SVPS.smv.onPlay(callback);
		};
		onPause = function(callback) {
			//callbacks.onPause.push(callback);
			SVPS.smv.onPause(callback);
		};

		// Since an automatic playlist change does not result in calling the public method next(),
		// but instead, its private counterpart, I cannot overwrite these with any effect. Instead,
		// I can use SMV's onComplete event handler.
		/*SVPS.smv.onComplete(function (e) {
			console.log('COMPLETE');
			deactivateElement('speaker');
			deactivateElement('topic');
		});*/

		// @TODO I should see at some point if these constructs are still necessary in current SMV engines
		// seek methods
		seek = function(topic) {
			// e.g. when BUFFERING or PAUSED (seek on IDLE doesn't stall SMV)
			var state = SVPS.smv.getStatus().toUpperCase();
			if (state !== 'PLAYING' && state !== 'IDLE') {
				// not all relevant onSeek events will trigger if player hasn't started
				applySeekOnPlay(topic.time, topic.playlist);
				SVPS.smv.play();
			} else {
				seekTime(topic.time, topic.playlist);
			}
		}
		seekTime = function(time, playlist) {
			if (playlist === getPlaylistIndex()) {
				// otherwise, smv player issues a warning
				playlist = null;
			}
			SVPS.smv.seek(time, playlist);
			if (playlist !== null) {
				resetHls(time, playlist);
			}
		}
	}

	/**
	 * SMV player initialization that is HLS/JS engine specific.
	 *
	 * @return void
	 */
	function initSmvMePlayer() {
		// SMV player changes the player id, so we should too if we want to get the actual HTML5 video tag
		select.playerObj = select.hlsWrapper + select.player;
		// reflect active player object
		SVPS.player = document.getElementById(select.playerObj);

		SVPS.smv.onReload(function(e) {
			resetMe();
		});

		// our own event listeners aren't destroyed, so we can keep these simple
		onTime = function(callback) {
			callbacks.onTime.push(callback);
		};
		onSeek = function(callback) {
			SVPS.smv.onSeek(callback);
		};
		// onPlay and onPause survive a playlist change, but not Quality or Audio change
		onPlay = function(callback) {
			SVPS.smv.onPlay(callback);
		};
		onPause = function(callback) {
			SVPS.smv.onPause(callback);
		};

		// me-engine doesn't have an e parameter..
		eventHandler['onSeek'] = function(t, type) {
			return function(e) {
				var offset = SVPS.smv.getPosition();
				if (offset >= t.start && offset < t.end
					&& t.playlist === getPlaylistIndex()
				) {
					if (t.id !== active[type]) {
						activateElement(t.id, type);
					}
					// even if t.id === active.type, onSeekHit needs to be marked to prevent deactivation
					onSeekHit[type] = true;
				}
			};
		};
		eventHandler['onTime'] = function(t, type) {
			return function(e) {
				var position = SVPS.smv.getPosition();
				if (position >= t.start && position < (t.start+0.4)
					&& t.id !== active[type]
					&& t.playlist === getPlaylistIndex()
				) {
					activateElement(t.id, type);
				}
			};
		};

		// workaround for not being able to create an event listener
		onPlay(function() {
			if (active.pausePlayInterval === null) {
				active['pausePlayInterval'] = setInterval(executeOnTimeCallbacks, 400);
			}
		});
		onPause(function() {
			if (active.pausePlayInterval !== null) {
				clearInterval(active.pausePlayInterval);
			}
			active['pausePlayInterval'] = null;
		});

		// Since an automatic playlist change does not result in calling the public method next(),
		// but instead, its private counterpart, I cannot overwrite these with any effect. Instead,
		// I can use SMV's onComplete event handler.
		/*SVPS.smv.onComplete(function (e) {
			console.log('COMPLETE');
			deactivateElement('speaker');
			deactivateElement('topic');
		});*/

		// @TODO I should see at some point if these constructs are still necessary in current SMV engines
		// seek methods
		seek = function(topic) {
			// e.g. when BUFFERING or PAUSED (seek on IDLE doesn't stall SMV)
			var state = SVPS.smv.getStatus().toUpperCase();
			if (state !== 'PLAYING' && state !== 'IDLE') {
				// not all relevant onSeek events will trigger if player hasn't started
				applySeekOnPlay(topic.time, topic.playlist);
				SVPS.smv.play();
			} else {
				seekTime(topic.time, topic.playlist);
			}
		}
		seekTime = function(time, playlist) {
			if (playlist === getPlaylistIndex()) {
				// otherwise, smv player issues a warning
				playlist = null;
			}
			SVPS.smv.seek(time, playlist);
			if (playlist !== null) {
				resetMe(time, playlist);
			}
		}
	}

	/**
	 * SMV player initialization that is HTML5 engine specific.
	 * - used by mobile devices.
	 *
	 * @return void
	 */
	function initSmvHtml5Player() {
		// SMV player changes the player id, so we should too if we want to get the actual HTML5 video tag
		select.playerObj = select + select.player + select.html5Wrapper;
		// reflect active player object
		SVPS.player = document.getElementById(select.playerObj);
		initHtml5EventListeners();

		// our own event listeners aren't destroyed, so we can keep these simple
		onTime = function(callback) {
			callbacks.onTime.push(callback);
		};
		onSeek = function(callback) {
			callbacks.onSeek.push(callback);
		};
		// onPlay and onPause survive a playlist change, but not Quality or Audio change
		onPlay = function(callback) {
			callbacks.onPlay.push(callback);
			SVPS.smv.onPlay(callback);
		};
		onPause = function(callback) {
			callbacks.onPause.push(callback);
			SVPS.smv.onPause(callback);
		};

		// Original smv property references, for those we need to overrule
		var orig = {};
		// overrule player.setQualityLevel()
		orig.setQualityLevel = SVPS.smv.setQualityLevel;
		SVPS.smv.setQualityLevel = function(q) {
			var time = SVPS.player.currentTime;
			var playlist = getPlaylistIndex();
			orig.setQualityLevel(q);
			resetHtml5(time, playlist);
		};
		// overrule player.setAudioLanguage()
		orig.setAudioLanguage = SVPS.smv.setAudioLanguage;
		SVPS.smv.setAudioLanguage = function(l) {
			var time = SVPS.player.currentTime;
			var playlist = getPlaylistIndex();
			orig.setAudioLanguage(l);
			resetHtml5(time, playlist);
		};
		// Since an automatic playlist change does not result in calling the public method next(),
		// but instead, its private counterpart, I cannot overwrite these with any effect. Instead,
		// I can use SMV's onComplete event handler.
		SVPS.smv.onComplete(function (e) {
			deactivateElement('speaker');
			deactivateElement('topic');
		});

		// seek methods
		seek = function(topic) {
			// note! SMVplayer playlist change on seek is BUGGY @ state !== 'PLAYING'
			var playlist = topic.playlist === getPlaylistIndex() ? null : topic.playlist;
			if (SVPS.smv.getStatus() === 'PLAYING') {
				seekTime(topic.time, playlist);
			} else if (playlist === null) {
				applySeekOnPlay(topic.time, null);
				SVPS.smv.play();
			} else {
				// @LOW we don't support this because of bugs:
				// - when player hasn't started yet, smvplayer loses track of his actual playlist item
				// - when player has started, applyOnSeek will not seek?
				log(logMsg.no_playlist_seek, true);
			}
		}
		seekTime = function(time, playlist) {
			SVPS.smv.seek(time, playlist);
		}
	}

	/**
	 * Create the video player by using the jwplayer object
	 * - Should be called by a specific JW version initializer
	 *
	 * @param data object Parsed JSON data
	 * @return void
	 */
	function initJwPlayerShared(data) {
		jwplayer(select.player).setup(data);
		// apparently setup() creates a new object, so assign these AFTER setup()
		SVPS.jw = jwplayer(select.player);
		// reflect active player object
		SVPS.player = SVPS.jw;

		// just use JW player internal functions
		onTime = function(callback) {
			SVPS.jw.onTime(callback);
		};
		onSeek = function(callback) {
			SVPS.jw.onSeek(callback);
		};
		onPlay = function(callback) {
			SVPS.jw.onPlay(callback);
		};
		onPause = function(callback) {
			SVPS.jw.onPause(callback);
		};
		getPlaylistIndex = function() {
			return SVPS.jw.getPlaylistIndex();
		}
		playOnReady = function() {
			SVPS.jw.onReady(function() {
				SVPS.jw.play();
			});
		}
		stop = function() {
			SVPS.jw.stop();
		}

		// on changing playlist item, deactivate elements
		SVPS.jw.onPlaylistItem(function(e) {
			deactivateElement('speaker');
			deactivateElement('topic');
		});

		// seek methods
		seek = function(topic) {
			// e.g. when IDLE or BUFFERING (seek on IDLE stalls JW)
			if (SVPS.jw.getState().toUpperCase() !== 'PLAYING') {
				// not all relevant onSeek events will trigger if player hasn't started
				applySeekOnPlay(topic.time, topic.playlist);
				SVPS.jw.play();
			} else {
				seekTime(topic.time, topic.playlist);
			}
		}

		// meetingdata refers to streamfile id's, which are unknown to JW player, hence a workaround:
		if (data.hasOwnProperty('playlist')) {
			var playlistData = formatPlaylistData(data.playlist);
			getPlaylistIndexFromTimeObject = function(time) {
				return playlistData[time.streamfileId];
			}
		} else {
			// note that if data.playlist is missing, the Streamovations REST response has changed and meetingdata will break
			log(logMsg.no_playlist, true)
			meetingdata.topic = false;
			meetingdata.speaker = false;
		}
	}

	/**
	 * JW player 7.x initialization
	 *
	 * @param data object Parsed JSON data
	 * @return boolean True on success, false on failure
	 * @see https://github.com/jwplayer/jwplayer/wiki/2.1-JW-Player-7-API-Changes
	 */
	function initJw7Player(data) {
		if (initJwPlayerVariables(true)) {
			initJwPlayerShared(data);

			// remaining seek method
			seekTime = function(time, playlistId) {
				if (getPlaylistIndex() !== playlistId) {
					SVPS.jw.once('playlistItem', function() {
						SVPS.jw.once('play', function() {
							SVPS.jw.seek(time);
						});
					});
					SVPS.jw.playlistItem(playlistId);
				} else {
					SVPS.jw.seek(time);
				}
			}

			// replace original function, as we can use the once() method
			applySeekOnPlay = function(time, playlist) {
				SVPS.jw.once('play', function() {
					seekTime(time, playlist);
				});
			}
			return true;
		}
		return false;
	}

	/**
	 * JW player 6.x initialization
	 *
	 * @param data object Parsed JSON data
	 * @return boolean True on success, false on failure
	 */
	function initJw6Player(data) {
		if (initJwPlayerVariables(false)) {
			initJwPlayerShared(data);

			// remaining seek methods
			seekTime = function(time, playlistId) {
				if (getPlaylistIndex() !== playlistId) {
					seekOnPlaylist[playlistId] = true;
					SVPS.jw.onPlaylistItem(function(e) {
						// similar construction to applySeekOnPlay, unfortunately
						if (seekOnPlaylist[playlistId]) {
							SVPS.jw.seek(time);
							seekOnPlaylist[playlistId] = false;
						}
					});
					SVPS.jw.playlistItem(playlistId);
				} else {
					SVPS.jw.seek(time);
				}
			}
			return true;
		}
		return false;
	}

	// @TODO turn around the init conditions: return false if true
	/**
	 * Actual SVPS object, offers public methods/properties
	 *
	 * @var object
	 */
	var SVPS = {

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
		 * Wrapper to substitute smvplayer object for smvplayer-only calls
		 * Keep public for debugging
		 *
		 * @var object
		 */
		smv: null,

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
			// @TODO specificity!
			var $playerContainer = $('.' + select.playerContainer),
			// @TODO specificity!
				$data = $('#' + select.data).first();
			if (!$playerContainer.exists() || !$data.exists()) {
				// at least one of necessary elements does not exist
				log(logMsg.no_player_data, true);
				return false;
			}

			// read & parse data
			var data = null,
				config = null;
			try {
				data = JSON.parse($data.html().trim());
			} catch (e) {
				log(logMsg.no_json_support, true);
				return false;
			}
			// @TODO possibly place in var, and pass it to init method on next call instead?
			//$data.html('');
			if (typeof data !== 'object') {
				log(logMsg.player_data_invalid, true);
				return false;
			}

			// determine if this is a livestream
			if (data.hasOwnProperty('application')) {
				this.isLiveStream = data.application === 'rtplive';
			}

			// if player element exists, we can initialize the actual player object
			var $player = $('#' + select.player, $playerContainer);
			if ($player.exists()) {
				try {
					// Supports multiple player types
					switch (playerType) {
						case 3:
							var $config = $('#' + select.config).first();
							if ($config.exists()) {
								config = JSON.parse($config.html().trim());
							}
							if (!initSmvPlayer(data, config)) {
								return false;
							}
							break;
						case 2:
							if (!initJw7Player(data)) {
								return false;
							}
							break;
						case 1:
							if (!initJw6Player(data)) {
								return false;
							}
							break;
						default:
							// no valid player configuration
							log(logMsg.invalid_player, true);
							return false;
					}

					// even though the player has its own onPlay events, adding
					// more general jQuery triggers can help when code needs
					// to refer to these events before SVPS is defined
					onPlay(function() {
						$playerContainer.trigger('SVPS:play');
					});

					if (this.isLiveStream) {
						// note that event handlers are not initialized during a livestream:
						// this is because a livestream is fed via polling and not via interaction
						if (meetingdata.topic || meetingdata.speaker || meetingdata.breaks) {
							initLiveCounters();
							initPolling(false);
						}
					} else {
						initEventHandlers();
					}
				} catch (e) {
					log(e, true);
				}
			} else if (this.isLiveStream && meetingdata.breaks) {
				// if the player element does not exist, we might be dealing with an interrupted livestream.
				// in that case: initiate polling anyway
				active.eventBreak = true;
				initLiveCounters();
				initPolling(true);
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
			if (meetingdata.breaks) {
				// check for new breaks
				if (data.hasOwnProperty('eventBreaks')
					&& data.eventBreaks !== false
					&& data.eventBreaks.length > 0
				) {
					processLatestEventBreak(data.eventBreaks);
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
				seek(topic);
			} else {
				log(logMsg.no_timestamp, true);
			}
		}
	};

	return SVPS;
})(jQuery);