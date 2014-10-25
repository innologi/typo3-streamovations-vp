<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Innologi.' . $_EXTKEY,
	'Video',
	array(
		'Video' => 'list, show',
		
	),
	// non-cacheable actions
	array(
		
	)
);
