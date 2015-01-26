<?php
namespace Innologi\StreamovationsVp\Utility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 * Much used initialization methods missing from
 * \TYPO3\CMS\Frontend\Utility\EidUtility
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Eid {

	/**
	 * Load and initialize TypoScriptFrontendController aka TSFE.
	 *
	 * Use this only if you require the availability frontend TypoScript.
	 *
	 * If you've initialized feUser elsewhere, you can optionally supply
	 * it here so that it won't be recreated.
	 *
	 * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $feUser
	 * @return void
	 */
	static public function initTSFE(\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $feUser = NULL) {
		// get page id
		$pid = (int)GeneralUtility::_GP('id');
		if (!$pid) {
			// @TODO throw error
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

		// TCA isn't necessary for all eID scripts that depend on TSFE, but the
		// existence of the following array is required in a RootlineUtility method
		if (!isset($GLOBALS['TCA']['pages']['columns'])) {
			$GLOBALS['TCA']['pages']['columns'] = array();
		}

		// initializes feUser used to determine various settings necessary for determineId()
		if ($feUser === NULL) {
			$tsfe->initFEuser();
		} else {
			$tsfe->fe_user = $feUser;
		}

		// initializes rootLine used by getConfigArray() to determine applicable TS records
		#$tsfe->determineId()
		// calling fetch_the_id() is faster than determineId(), although @access is set to private
		$tsfe->fetch_the_id();
		// initializes tmpl which getConfigArray() uses to store TS in
		$tsfe->initTemplate();
		// sets TS in tmpl->setup for use by configurationManager
		$tsfe->getConfigArray();
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
	 * @return void
	 */
	static public function initExtbaseBootstrap($pluginName = '', $extensionName = '', $vendorName = '') {
		$bootstrap = new Bootstrap();
		$bootstrap->initialize(array(
			'pluginName' => $pluginName,
			'extensionName' => $extensionName,
			'vendorName' => $vendorName
		));
	}

}
