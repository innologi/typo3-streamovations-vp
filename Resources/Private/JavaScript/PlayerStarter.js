var PlayerStarter = {
	// set license key for JW Player
	setLicenseKey: function(licenseKey) {
		if (!jwplayer) {
			alert('###ERROR_NO_JWPLAYER###');
			return;
		}
		if (licenseKey) {
			jwplayer.key = licenseKey;
		}
		// else silently fail
	},
	// initialize JW Player
	initJwPlayer: function() {
		jwplayer('tx-streamovations-vp-play').setup({
			//file: 'http://188.205.234.147:1935/vod/19_15_test_scheduler_e07efb7cbaa993768b376864e08ad0328a451c32.mp4'
			//file: '188.205.234.147:1935/vod/19_15_test_scheduler_caef3c4f1d9c4255691b18d1b92bad86cf3dbe66---mbr-2---.mp4'
			file: 'rtmp://188.205.234.147:1935/vod/19_15_test_scheduler_caef3c4f1d9c4255691b18d1b92bad86cf3dbe66---mbr-2---.mp4'
			//image: '/uploads/myPoster.jpg'
		});
	},
	// initialize HTML5 Player
	initHtml5Player: function() {
		if (!!document.createElement('video').canPlayType) {
			// fallback
			PlayerStarter.initJwPlayer();
		}
	},
	// initialize Streamovations Player
	initSmvPlayer: function(jsonString) {
		if (!window.JSON.parse) {
			// fallback
			PlayerStarter.initJwPlayer();
			return;
		}
		if (!jwplayer.key) {
			alert('###ERROR_NO_KEY###');
			return;
		}
		if (!jsonString) {
			alert('###ERROR_NO_JSON###');
			return;
		}
		if (!smvplayer) {
			alert('###ERROR_NO_SMVPLAYER###');
			return;
		}

		var jsonData = JSON.parse(jsonString);
		if (typeof jsonData !== 'object') {
			alert('###ERROR_INVALID_JSON###');
			return;
		}

		smvplayer('tx-streamovations-vp-play').init(jsonData);
	}
}

PlayerStarter.setLicenseKey('###JWPLAYER_KEY###');
