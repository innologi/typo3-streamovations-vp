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
		$timelineConfig = array(
			'filterList' => 'last',
			'json' => 0
		);

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
						'meetingdata' => array(
							'response' => array(
								'property' => array(
									'topicTimeline' => $timelineConfig,
									'speakerTimeline' => $timelineConfig
								)
							)
						)
					)
				)
			)
		);
	}

	/**
	 * Run eID script and return output
	 *
	 * @return string
	 */
	public function run() {
		$hash = GeneralUtility::_GP('hash');
		if (!$hash) {
			// @TODO throw error
		}

		/* @var $meetingdataRepository \Innologi\StreamovationsVp\Domain\Repository\MeetingdataRepository */
		$meetingdataRepository = $this->objectManager->get('Innologi\\StreamovationsVp\\Domain\\Repository\\MeetingdataRepository');
		//$meetingdataRepository->setForceRawResponse(TRUE);
		$meetingdata = $meetingdataRepository->findByHash($hash);

		// why even bother with TYPO3 session in eID context..
		session_start();
		$objectHash = md5(serialize($meetingdata));
		$sessionKey = 'meetingdataHash' . $hash;
		if (!isset($_SESSION[$sessionKey]) || $objectHash !== $_SESSION[$sessionKey]) {
			$_SESSION[$sessionKey] = $objectHash;
		} else {
			// on no changes, providing an empty class speeds things up
			$meetingdata = new \stdClass();
		}

		return json_encode($meetingdata);
	}
}

$eID = GeneralUtility::makeInstance(__NAMESPACE__ . '\\Meetingdata');
echo $eID->run();
