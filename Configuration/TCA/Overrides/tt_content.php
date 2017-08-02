<?php
defined('TYPO3_MODE') or die();

// add the flexform
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
	'streamovationsvp_video',
	'FILE:EXT:streamovations_vp/Configuration/FlexForms/flexform_video.xml'
);
# @CGL do we still need this?
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['streamovationsvp_video'] = 'pi_flexform';


// register the plugin
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'Innologi.StreamovationsVp',
	'Video',
	'Streamovations Video Player'
);
