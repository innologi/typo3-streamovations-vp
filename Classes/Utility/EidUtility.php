<?php
namespace Innologi\StreamovationsVp\Utility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Core\Bootstrap;
/**
 * eID utility class
 *
 * Expands original eID utility class with much used
 * initialization methods that were missing.
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class EidUtility {

	/**
	 * Load and initialize TypoScriptFrontendController aka TSFE.
	 *
	 * Use this only if you require the availability frontend TypoScript.
	 *
	 * If you've initialized feUser elsewhere, you can optionally supply
	 * it here so that it won't be recreated.
	 *
	 * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $feUser
	 * @throws \Exception
	 * @return void
	 */
	static public function initTSFE(\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $feUser = NULL) {
		// get page id
		$pid = (int)GeneralUtility::_GP('id');
		if (!$pid) {
			throw new \Exception('A page-id needs to be provided as \'id\' parameter, to retrieve active frontend configuration.');
		}

		// initialize TSFE
		/* @var $tsfe \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController */
		$GLOBALS['TSFE'] = $tsfe = GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
			$GLOBALS['TYPO3_CONF_VARS'],
			$pid,
			0,
			TRUE
		);

		// TCA isn't necessary for all eID scripts that depend on TSFE, but ..
		if (!isset($GLOBALS['TCA']['pages'])) {
			$GLOBALS['TCA']['pages'] = [
				// required due to PageRepository->enableFields() in TYPO3 7.x
				'ctrl' => [],
				// required due to a RootlineUtility method in TYPO3 6.x
				'columns' => []
			];
		}
		// @LOW I think it's safe to put this in the TCA pages if scope above, but I need to verify, because it looks like this one is needed because pages DOES contain data, so it could be that pages already exists (or is added to later)
		// same goes for these when it is necessary to generate the cObj outselves:
		if (!isset($GLOBALS['TCA']['sys_file_reference'])) {
			$GLOBALS['TCA']['sys_file_reference'] = [
				'ctrl' => [],
				'columns' => []
			];
		}

		// initializes feUser used to determine various settings necessary for determineId()
		#if ($feUser === NULL) {
		#	$tsfe->initFEuser();
		#} else {
		#	$tsfe->fe_user = $feUser;
		#}

		// initializes rootLine used by getConfigArray() to determine applicable TS records
		#$tsfe->determineId()
		// calling fetch_the_id() is faster than determineId(), although @access is set to private
		$tsfe->fetch_the_id();
		// sets TS in tmpl->setup for use by configurationManager
		$tsfe->getConfigArray();

		// make sure cObj exists
		if (!is_object($tsfe->cObj)) {
			$tsfe->newCObj();
		}
	}

	/**
	 * Initializes Extbase Bootstrap
	 *
	 * Use this only if your eID class needs to work with
	 * extbase contexts, like repositories.
	 *
	 * Providing parameters will allow you to prepare configuration
	 * for context of a specific plugin or extension.
	 *
	 * @param string $pluginName
	 * @param string $extensionName
	 * @param string $vendorName
	 * @param array $configuration
	 * @return void
	 */
	static public function initExtbaseBootstrap($pluginName = '', $extensionName = '', $vendorName = '', array $configuration = array()) {
		$configuration = array_merge(array(
			'pluginName' => $pluginName,
			'extensionName' => $extensionName,
			'vendorName' => $vendorName
		), $configuration);
		$bootstrap = GeneralUtility::makeInstance(Bootstrap::class);
		$bootstrap->initialize($configuration);
	}

}
