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
		// @TODO need to decide which ones get cached and which ones not
		'Video' => 'list, presetShow, show, liveStream',
	)
);