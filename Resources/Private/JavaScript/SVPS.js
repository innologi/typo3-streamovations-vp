// #@TODO license + author info
/*
 * Streamovations Video Player Starter (SVPS)
 * ------------------------------------------
 *
 * Please note that ALL properties are for internal use only,
 * any property that is configurable will have an respective
 * TypoScript setting.
 *
 * Regardless, all element properties are publicly visible
 * (thus modifiable) to make debugging via browser tools
 * as easy as possible.
 */
var SVPS = (function($) {

	// extend jQuery
	$.fn.exists = function () {
		return this.length !== 0;
	}

	// actual SVPS object
	var _this = {

		// wrapper to substitute the actual player object
		player: null,
		// wrapper to substitute jwplayer object for jwplayer-only calls
		jw: null,

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
			playlist: 0,
			topic: 0,
			speaker: 0
		},

		// original property references, for those we need to overrule
		orig: {
			player: {
				next: null,
				previous: null
			}
		},

		// callback arrays for each event handler
		callbacks: {
			onTime: [],
			onSeek: []
		},

		// used to limit seekOnPlays to 1
		seekOnPlay: false,

		// registers a hit of onSeek events, to determine if topics/speakers need to be disabled
		onSeekHit: {
			topic: false,
			speaker: false
		},

		// init / start function
		init: function() {
			// check if necessary elements exist
			var $player = $('#' + this.select.player),
				$data = $('#' + this.select.data + ':first');
			if (!$player.exists() || !$data.exists()) {
				// at least one of necessary elements does not exist
				return false;
			}

			switch ('###PLAYER_TYPE###') {
				case '2':
					// read data
					var data = $data.html();
					$data.html('');

					if (window.JSON.parse) {
						this.createSmvPlayer(data);
						break;
					}
					// incompatible user-agent, fallback to jw
				case '1':
					this.createJwPlayer();
					break;
				default:
					// no valid player configuration
					alert('###ERROR_NO_VALIDPLAYER###');
					return false;
			}

			this.initEventHandlers();
			return true;
		},

		// initialize JW Player
		initJwPlayer: function(requireLicense) {
			var licenseKey = '###JWPLAYER_KEY###';
			// #@TODO test these kind of errors btw
			if (!jwplayer) {
				alert('###ERROR_NO_JWPLAYER###');
				return false;
			}

			if (licenseKey) {
				jwplayer.key = licenseKey;
			} else if(requireLicense) {
				alert('###ERROR_NO_KEY###');
				return false;
			}
			return true;
		},

		// initialize Streamovations Player
		createSmvPlayer: function(jsonString) {
			// #@FIX do a more proper error handling
			if (this.initJwPlayer(true)) {
				// smvplayer requires an existing jsonString
				if (!jsonString) {
					alert('###ERROR_NO_JSON###');
					return;
				}
				// smvplayer object needs to exist
				// #@FIX this produced an exception when smvplayer wasn't loaded, so this doesn't work
				if (!smvplayer) {
					alert('###ERROR_NO_SMVPLAYER###');
					return;
				}

				// @TODO try/catch for every JSON.parse call?
				// parse json
				var jsonData = JSON.parse(jsonString);
				if (typeof jsonData !== 'object') {
					alert('###ERROR_INVALID_JSON###');
					return;
				}

				// meetingdata refers to streamfile id's, but we can only request playlist id from jwplayer object
				if (jsonData.hasOwnProperty('playlist')) {
					this.pushPlaylistToIdMap(jsonData.playlist);
				}

				this.player = smvplayer(this.select.player);
				this.player.init(jsonData);
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
			}
		},

		// #@TODO finish implementation!
		// initialize JW Player
		createJwPlayer: function() {
			if (this.initJwPlayer(false)) {
				this.player = jwplayer(this.select.player);
				this.player.setup({
					file: 'rtmp://188.205.234.147:1935/vod/19_15_test_scheduler_caef3c4f1d9c4255691b18d1b92bad86cf3dbe66---mbr-2---.mp4'
					//image: '/uploads/myPoster.jpg'
				});
				this.jw = this.player;
			}
		},

		// initialize Native HTML5 Player
		createNativeHtml5Player: function() {
			// #@LOW finish implementation?
			if (!!document.createElement('video').canPlayType) {
				// fallback
			}
			console.log('ERROR: unfinished implementation');
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
			// set jump event on topic clicks
			$('.' + this.select.container + ' .topics').on('click', '.topic .topic-link', function() {
				_this.jumpToTopic(
					$(this).parent('.topic').attr('data-topic')
				);
				return false;
			});

			// automatic timeline events need JSON parsing
			if (window.JSON.parse) {
				// parse meeting data
				var $topicTimeline = $('#' + this.select.topicTimeline + ':first');
				var $speakerTimeline = $('#' + this.select.speakerTimeline + ':first');

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
							_this.activateElement(t.id, type);
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

		// needs to be called when the jwplayer instance was removed and a new instance created
		reset: function() {
			this.jw = jwplayer(this.select.player);
			// if really new, an onReady will be fired
			this.jw.onReady(function(e) {
				// @FIX only smvplayer has getTimeline()
				_this.active.playlist = _this.player.getTimeline().currentItem;
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
			console.log('SVPS | Reattached event callbacks');
		},

		// deactivate all topics/speakers
		deactivateElement: function(type) {
			$('.' + this.select.container + ' .' + type + 's .' + type + '.active').removeClass('active');
			this.active[type] = 0;
		},

		// activate topic/speaker
		activateElement: function(id, type) {
			this.deactivateElement(type);
			$('.' + this.select.container + ' .' + type + 's .' + type + '[data-' + type + '=' + id + ']:first').addClass('active');
			this.active[type] = id;
			console.log('SVPS | Activated ' + type + ' ' + id);
		},

		// jump to the correct topic after finding its playlist item
		jumpToTopic: function(id) {
			var topic = this.idMap.topic[id];
			if (topic !== undefined) {
				// @TODO this is entirely smvplayer dependent, so what to do with jwplayer?
				var moveAction = topic.playlist - this.player.getTimeline().currentItem;
				if (moveAction > 0) {
					this.recursiveSeekInNext(0, moveAction, topic.time);
				} else if(moveAction < 0) {
					this.recursiveSeekInPrevious(0, moveAction, topic.time);
				} else {
					if (this.jw.getState() === 'IDLE') {
						// not all relevant onSeek events will trigger if player hasn't started
						this.applySeekOnPlay(topic.time);
						this.jw.play(true);
					} else {
						this.player.seek(topic.time);
					}
				}
			} else {
				// @LOW throw error?
				console.log('SVPS | Topic has no registered timestamps');
			}
		},

		// recursively call next() and then seek()
		recursiveSeekInNext: function(current, limit, time) {
			this.player.next();
			current++;
			if (current < limit) {
				this.jw.onReady(function (e) {
					// timeout prevents flash from crashing
					setTimeout(function() {
						_this.recursiveSeekInNext(current, limit, time);
					}, 50);
				});
			} else {
				this.applySeekOnPlay(time);
			}
		},

		// recursively call previous() and then seek()
		recursiveSeekInPrevious: function(current, limit, seekTime) {
			this.player.previous();
			current--;
			if (current > limit) {
				// timeout prevents flash from crashing
				this.jw.onReady(function (e) {
					setTimeout(function() {
						_this.recursiveSeekInPrevious(current, limit, seekTime);
					}, 50);
				});
			} else {
				this.applySeekOnPlay(seekTime);
			}
		},

		// applies a seek() on the onPlay event handler, useful when player isn't playing
		applySeekOnPlay: function(seekTime) {
			this.seekOnPlay = true;
			this.jw.onPlay(function (e) {
				if (_this.seekOnPlay) {
					_this.player.seek(seekTime);
					_this.seekOnPlay = false;
				}
			});
		},

		// find out if time is valid for current playlist item
		timeIsOnPlaylist: function(t) {
			return this.idMap.playlist[t.streamfileId] === this.active.playlist;
		}

	}

	// initialize when document is loaded
	$(document).ready(function() {
		// @TODO this should be done outside of this script via SVPS.init();
		_this.init();
	});

	return _this;
})(jQuery);