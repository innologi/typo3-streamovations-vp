<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Video',
	'Streamovations Video Player'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
	$_EXTKEY,
	'Configuration/TypoScript',
	'Streamovations Video Player'
);

// add plugin flexform
$pluginSignature = str_replace('_','',$_EXTKEY) . '_video';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
	$pluginSignature,
	'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_video.xml'
);

// add plugin flexform csh
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tt_content.pi_flexform.'.$pluginSignature.'.list',
	'EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang_csh_flexform_video.xml'
);