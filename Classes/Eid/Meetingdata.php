<?php
namespace Innologi\StreamovationsVp\Eid;
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
use Innologi\StreamovationsVp\Utility\EidUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * Meetingdata eID script
 *
 * Used for retrieving and returning json-encoded meetingdata
 * from its repository.
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Meetingdata {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->initializeContext();
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
	}

	/**
	 * Initializes the necessary contexts for the eID script.
	 *
	 * @return void
	 */
	protected function initializeContext() {
		$responsePropertyConfig = array(
			'filterList' => 'last',
			'json' => 0
		);

		// @TODO deprecated @ v9.4: https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85878-EidUtilityAndVariousTSFEMethods.html
			// @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.2/Feature-83725-SupportForPSR-15HTTPMiddlewares.html
			// @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.2/Deprecation-83803-DeprecateEidRequestHandler.html
		// initialize TSFE to make TS accessible to extbase configuration manager
		EidUtility::initTSFE();
		// initialize extbase bootstrap, so we can use repositories
		EidUtility::initExtbaseBootstrap(
			'Video',
			'StreamovationsVp',
			'Innologi',
			// overrule property json encoding
			array(
				'rest' => array(
					'repository' => array(
						'Meetingdata' => array(
							'features' => array(
								// If we don't disable it, the changed configurations of
								// the timelines conflict with the domain models and thus
								// reflection would cause errors in the response mapper.
								//
								// Also: this gives us better performance in this use-case.
								'disableResponseMapper' => 1
							),
							'response' => array(
								'property' => array(
									'topicTimeline' => $responsePropertyConfig,
									'speakerTimeline' => $responsePropertyConfig,
								)
							),
							// cache concurrent requests to Streamovations API to lighten its load
							'cache' => $this->getOptimalCacheValues()
						)
					)
				)
			)
		);
	}

	/**
	 * Returns cache settings based on the polling interval used to call
	 * this specific eID script.
	 *
	 * Note that when lifetime===interval, testresults show that the second polling call
	 * of the visitor responsible for the cache, will result in him retrieving his own
	 * cache result once, which makes the current polling interval completely useless.
	 * To remedy this we do lifetime=interval-1.
	 *
	 * If interval was already at 1, we can only force-disable caching to keep the chosen
	 * interval remaining effective.
	 *
	 * @throws \Exception
	 * @return array
	 */
	protected function getOptimalCacheValues() {
		// note that this isn't the extbase way, but it's a hell of a lot quicker, which is the point of eID
		if (!isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_streamovationsvp.']['settings.']['polling.']['interval'][0])) {
			throw new \Exception('Could not determine polling interval.');
		}

		$cacheSettings = array();
		$cacheSettings['lifetime'] = ((int) $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_streamovationsvp.']['settings.']['polling.']['interval']) - 1;
		if ($cacheSettings['lifetime'] < 1) {
			$cacheSettings['enable'] = 0;
		}
		return $cacheSettings;
	}

	/**
	 * Run eID script and return output
	 *
	 * @throws \Exception
	 * @return string
	 */
	public function run() {
		$hash = GeneralUtility::_GP('hash');
		if (!$hash) {
			throw new \Exception('A session hash needs to be provided as \'hash\' parameter, to retrieve relevant meetingdata.');
		}
		// if set, will force to return meetingdata, regardless of session content
		$force = (int)GeneralUtility::_GP('force');

		/* @var $meetingdataRepository \Innologi\StreamovationsVp\Domain\Repository\MeetingdataRepository */
		$meetingdataRepository = $this->objectManager->get('Innologi\\StreamovationsVp\\Domain\\Repository\\MeetingdataRepository');
		$meetingdata = $meetingdataRepository->findByHash($hash);

		// why even bother with TYPO3 session in eID context..
		session_start();
		$objectHash = md5(serialize($meetingdata));
		$sessionKey = 'meetingdataHash' . $hash;
		if ($force || !isset($_SESSION[$sessionKey]) || $objectHash !== $_SESSION[$sessionKey]) {
			$_SESSION[$sessionKey] = $objectHash;
		} else {
			// on no changes, providing an empty class speeds things up
			$meetingdata = new \stdClass();
		}

		// json_encode automatically won't include MagicResponse->$__properties
		return json_encode($meetingdata);
	}
}

$eID = GeneralUtility::makeInstance(__NAMESPACE__ . '\\Meetingdata');
echo $eID->run();
