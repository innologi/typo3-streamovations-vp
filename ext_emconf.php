<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "streamovations_vp"
 *
 * Auto generated by Extension Builder 2014-10-22
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Streamovations Video Player',
	'description' => 'Streaming Video Player (JW player or JW player-based) that connects to your Streamovations instance for live-streaming, VOD and additional (interactive) metadata (e.g. topic, speaker)',
	'category' => 'plugin',
	'author' => 'Frenck Lutke',
	'author_email' => 'typo3@innologi.nl',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '2.0.5',
	'constraints' => array(
		'depends' => array(
			'typo3' => '8.7.0-8.7.99',
			'php' => '7.1'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'autoload' => array(
		'psr-4' => array(
			'Innologi\\StreamovationsVp\\' => 'Classes'
		)
	)
);