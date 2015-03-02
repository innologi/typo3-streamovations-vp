<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Innologi.' . $_EXTKEY,
	'Video',
	array(
		'Video' => 'list, presetShow, show, liveStream',
	),
	// non-cacheable actions
	array(
		// because all actions draw their data from a rest-service,
		// we're not caching any of them. instead, we rely on a
		// caching table with configurable caching lifetime per
		// rest-repository
		'Video' => 'list, presetShow, show, liveStream',
	)
);

// create a cache specifically for rest requests
if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['streamovations_vp_rest'])
	|| !is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['streamovations_vp_rest'])
) {
	$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['streamovations_vp_rest'] = array(
		'options' => array(
			'defaultLifetime' => 3600,
			'compression' => extension_loaded('zlib')
		),
		'groups' => array('pages', 'all')
	);
}

// register eID script for metadata processing
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'][$_EXTKEY . '_meetingdata'] =
'EXT:' . $_EXTKEY . '/Classes/Eid/Meetingdata.php';