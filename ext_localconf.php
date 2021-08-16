<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Innologi.Streamovations_Vp',
	'Video',
	array(
		'Video' => 'list, presetShow, show, liveStream, advancedShow'
	),
	// @LOW review if we absolutely can't cache presetShow, show and advancedShow
	// non-cacheable actions
	array(
		// because all actions draw their data from a rest-service,
		// we're not caching any of them. instead, we rely on a
		// caching table with configurable caching lifetime per
		// rest-repository
		'Video' => 'list, presetShow, show, liveStream, advancedShow',
	)
);

// create a cache specifically for rest requests
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['streamovations_vp_rest'])
	|| !is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['streamovations_vp_rest'])
) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['streamovations_vp_rest'] = array(
		'options' => array(
			'defaultLifetime' => 3600,
			'compression' => extension_loaded('zlib')
		),
		'groups' => array('pages', 'all')
	);
}

// register eID script for metadata processing
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['streamovations_vp_meetingdata'] = \Innologi\StreamovationsVp\Eid\Meetingdata::class;

// custom PageTitle provider
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(trim('
    config.pageTitleProviders {
        streamovations_vp {
            provider = Innologi\StreamovationsVp\Seo\HashTitleProvider
            before = altPageTitle,record,seo
        }
    }
'));

// register implementation classes for DI
/** @var \TYPO3\CMS\Extbase\Object\Container\Container $container */
$container = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class);
$container->registerImplementation(
	\Innologi\StreamovationsVp\Library\RestRepository\ResponseInterface::class,
	\Innologi\StreamovationsVp\Library\RestRepository\MagicResponse::class
);
$container->registerImplementation(
	\Innologi\StreamovationsVp\Library\RestRepository\RequestInterface::class,
	\Innologi\StreamovationsVp\Library\RestRepository\Typo3Request::class
);

// @TODO replace all TEMPLATE cases with FLUIDTEMPLATE so this becomes unnecessary
// Add FILE alternative
$GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'] = array_merge(
	$GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'],
	[
		'STREAMOVATIONS_VP_FILE' => \Innologi\StreamovationsVp\Mvc\ContentObject\FileContentObject::class
	]
);
