<?php
namespace Innologi\StreamovationsVp\Controller;
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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Innologi\StreamovationsVp\Mvc\Controller\Controller;
use Innologi\StreamovationsVp\Seo\HashTitleProvider;
use Innologi\StreamovationsVp\Library\RestRepository\ResponseInterface;
use Innologi\StreamovationsVp\Library\RestRepository\Exception\HttpNotFound;
use Innologi\StreamovationsVp\Domain\Service\MeetingdataService;
use Innologi\StreamovationsVp\Exception\Configuration;
/**
 * Video Controller
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class VideoController extends Controller {
	// @TODO _____add a debugging-console feature to RestRepository, so that it logs entire responses to console?
	/**
	 * @var \Innologi\StreamovationsVp\Domain\Repository\EventRepository
	 */
	protected $eventRepository;

	/**
	 * @var \Innologi\StreamovationsVp\Domain\Repository\PlaylistRepository
	 */
	protected $playlistRepository;

	/**
	 * @var \Innologi\StreamovationsVp\Domain\Repository\MeetingdataRepository
	 */
	protected $meetingdataRepository;

	/**
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Repository\EventRepository $eventRepository
	 */
	public function injectEventRepository(\Innologi\StreamovationsVp\Domain\Repository\EventRepository $eventRepository)
	{
		$this->eventRepository = $eventRepository;
	}

	/**
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Repository\PlaylistRepository $playlistRepository
	 */
	public function injectPlaylistRepository(\Innologi\StreamovationsVp\Domain\Repository\PlaylistRepository $playlistRepository)
	{
		$this->playlistRepository = $playlistRepository;
	}

	/**
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Repository\MeetingdataRepository $meetingdataRepository
	 */
	public function injectMeetingdataRepository(\Innologi\StreamovationsVp\Domain\Repository\MeetingdataRepository $meetingdataRepository)
	{
		$this->meetingdataRepository = $meetingdataRepository;
	}

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 * @api
	 */
	protected function initializeAction() {
		parent::initializeAction();
		// at this point, the request is set and initialized, so we can pass it to the repositories
		if ($this->request->getInternalArgument('__noRestSettingsOverride') !== TRUE) {
			$this->eventRepository->setOriginalRequestParameters($this->request);
			$this->playlistRepository->setOriginalRequestParameters($this->request);
			$this->meetingdataRepository->setOriginalRequestParameters($this->request);
		}
	}

	/**
	 * Lists sessions
	 *
	 * @return void
	 */
	public function listAction() {
		try {
			$this->eventRepository
				->setCategory($this->settings['event']['category'])
				->setSubCategory($this->settings['event']['subCategory'])
				->setTags($this->settings['event']['tags']);
			// @LOW support time strings?
			// @LOW error handling of lack of proper dates?
			$events = NULL;
			if (isset($this->settings['event']['dateAt'][1])) {
				$dateTime = new \DateTime();
				$dateTime->setTimestamp((int) $this->settings['event']['dateAt']);
				$events = $this->eventRepository->findAtDate($dateTime);
			} else {
				$dateStart = NULL;
				if (isset($this->settings['event']['dateFrom'][1])) {
					$dateStart = new \DateTime();
					$dateStart->setTimestamp((int) $this->settings['event']['dateFrom']);
				}
				$dateEnd = NULL;
				if (isset($this->settings['event']['dateTo'][1])) {
					$dateEnd = new \DateTime();
					$dateEnd->setTimestamp((int) $this->settings['event']['dateTo']);
				}
				$events = $this->eventRepository->findBetweenDateTimeRange($dateStart, $dateEnd);
			}

			/* @var $eventService \Innologi\StreamovationsVp\Domain\Service\EventService */
			$eventService = $this->objectManager->get('Innologi\\StreamovationsVp\\Domain\\Service\\EventService');
			$events = $eventService->filterOutLiveStreams(
				$eventService->filterOutUnpublished($events)
			);

			$this->view->assign('events', $events);

			// pid fallback, seeing as how there is NONE unless we pass a NULL value
			// .. which is impossible in TYPO3 Fluid
			if (!isset($this->settings['showPid'][0])) {
				$this->settings['showPid'] = $GLOBALS['TSFE']->id;
				$this->view->assign('settings', $this->settings);
			}

		} catch(HttpNotFound $e) {
			// no streams found returns a 404, which in this case really isn't an error
			$this->addFlashMessage(
				LocalizationUtility::translate('no_list', $this->extensionName),
				LocalizationUtility::translate('no_list_header', $this->extensionName),
				FlashMessage::INFO,
				FALSE
			);
		}
	}

	/**
	 * Show playlist
	 *
	 * @param string $hash Playlist Hash
	 * @param boolean $isLiveStream
	 * @return void
	 */
	public function showAction($hash, $isLiveStream = FALSE) {
		if ( !(isset($this->settings['jwPlayer']) && is_array($this->settings['jwPlayer'])) ) {
			throw new Configuration(
				'Missing TypoScript jwPlayer settings. Please include the extension static TypoScript through your root TS Template record.'
			);
		}

		$playerType = (int)$this->settings['player'];

		// smvPlayer requires raw response
		if ($playerType === 3) {
			$this->playlistRepository->setForceRawResponse(TRUE);
		}

		$playerConfig = NULL;
		$playlist = $this->playlistRepository->findByHash($hash);
		if ($playlist) {
			/* @var $playlistService \Innologi\StreamovationsVp\Domain\Service\PlaylistService */
			$playlistService = $this->objectManager->get(
				'Innologi\\StreamovationsVp\\Domain\\Service\\PlaylistService',
				$this->extensionName
			);

			// smvPlayer supports additional options and configuration
			if ($playerType === 3) {
				if ( !(isset($this->settings['smvPlayer']) && is_array($this->settings['smvPlayer'])) ) {
					throw new Configuration(
						'Missing TypoScript smvPlayer settings. Please include the extension static TypoScript through your root TS Template record.'
					);
				}
				$playlist = $playlistService->alterSmvPlayerSetup($playlist, $this->settings['smvPlayer']);
				$playerConfig = $playlistService->createSmvPlayerConfig($this->settings['smvPlayer'], $this->settings['jwPlayer']);

			// jwPlayer
			} elseif ($playlist instanceof ResponseInterface) {
				// for jwPlayer we need to construct a valid configuration from the playlist-response
				$playlistData = $playlistService->createJwplayerSetup($playlist, $this->settings['jwPlayer']);
				$playerConfig = $playlistService->createJwplayerConfig($this->settings['jwPlayer']);

				// javascript JSON.parse already deals with escaped slashes, but
				// still I found it inconvenient to have them when debugging, so..
				$playlist = json_encode($playlistData, JSON_UNESCAPED_SLASHES);
			}

			// at least one of meetingdata types must be enabled before getting anything
			$meetingdata = NULL;
			if ((isset($this->settings['topics']['enable']) && (bool)$this->settings['topics']['enable'])
				|| (isset($this->settings['speakers']['enable']) && (bool)$this->settings['speakers']['enable'])
				|| ($isLiveStream && isset($this->settings['breaks']['enable']) && (bool)$this->settings['breaks']['enable'])
			) {
				$meetingdata = $this->meetingdataRepository->findByHash($hash);
				if ($isLiveStream && isset($this->settings['breaks']['enable']) && (bool)$this->settings['breaks']['enable']) {
					/** @var MeetingdataService $meetingdataService */
					$meetingdataService = $this->objectManager->get(MeetingdataService::class);
					$this->view->assign('interruptPlayer', $meetingdataService->isEventbreakActive($meetingdata));
				}
			}
			$this->view->assign('meetingdata', $meetingdata);
		}

		$this->view->assign('playerSetup', $playlist);
		if (!empty($playerConfig)) {
			$this->view->assign('playerConfig', json_encode($playerConfig, JSON_UNESCAPED_SLASHES));
		}

		if (isset($this->settings['hashHeader']) && (bool)$this->settings['hashHeader']) {
			/** @var HashTitleProvider $titleProvider */
			$titleProvider = $this->objectManager->get(HashTitleProvider::class);
			$titleProvider->setTitle($hash);
		}

		// @LOW we should autodetect this once we allow livestreams via list
		$this->view->assign('isLiveStream', $isLiveStream);
		$this->view->assign('hash', $hash);
		$this->view->assign('requestUri', $this->uriBuilder->getRequest()->getRequestUri());

		// unless __noBackPid was set, assign a back-page
		if ($this->request->getInternalArgument('__noBackPid') !== TRUE) {
			$this->view->assign(
				'backPid',
				isset($this->settings['backPid'][0])
					? $this->settings['backPid']
					: $GLOBALS['TSFE']->id
			);
		}
	}

	/**
	 * Show playlist by configured hash
	 *
	 * @return void
	 */
	public function presetShowAction() {
		if (isset($this->settings['playlist']['hash'][0])) {
			$arguments = array(
				'hash' => $this->settings['playlist']['hash'],
				'__noRedirectOnException' => TRUE,
				'__noBackPid' => TRUE
			);
			$this->forward('show', NULL, NULL, $arguments);
		}

		$this->addFlashMessage(
			LocalizationUtility::translate('config_missing_hash', $this->extensionName),
			LocalizationUtility::translate('config_error_header', $this->extensionName),
			FlashMessage::WARNING,
			FALSE
		);
	}

	/**
	 * Show LIVE stream
	 *
	 * @return void
	 */
	public function liveStreamAction() {
		try {
			$events = $this->eventRepository
				->setCategory($this->settings['event']['category'])
				->setSubCategory($this->settings['event']['subCategory'])
				->setTags($this->settings['event']['tags'])
				->findAtDateTime(new \DateTime());

			// although only LIVEstreams should be active @ requested time (=now), this is not
			// necessarily the case, as some tests have pointed out. So we need to filter.
			/* @var $eventService \Innologi\StreamovationsVp\Domain\Service\EventService */
			$eventService = $this->objectManager->get('Innologi\\StreamovationsVp\\Domain\\Service\\EventService');
			$event = $eventService->findFirstLiveStream($events);

			if ($event !== FALSE) {
				$arguments = array(
					'hash' => $event->getEventId(),
					'isLiveStream' => TRUE,
					'__noRestSettingsOverride' => TRUE,
					'__noRedirectOnException' => TRUE,
					'__noBackPid' => TRUE
				);
				$this->forward('show', NULL, NULL, $arguments);
			}
		} catch (HttpNotFound $e) {
			// no streams found returns a 404, which in this case really isn't an error
		}

		// no livestream available
		$this->addFlashMessage(
			LocalizationUtility::translate('no_livestream', $this->extensionName),
			LocalizationUtility::translate('no_livestream_header', $this->extensionName),
			FlashMessage::INFO,
			FALSE
		);
	}

	/**
	 * Show playlist as directed by typoscript configuration.
	 *
	 * This is flexible enough to configure it to use e.g.
	 * another ext's GET variables to determine which video to show.
	 *
	 * @return void
	 */
	public function advancedShowAction() {
		// @extensionScannerIgnoreLine false positive
		$contentObject = $this->configurationManager->getContentObject();
		$typoscript = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
		);
		$advanced = $typoscript['plugin.']['tx_streamovationsvp.']['settings.']['advanced.'];

		if (isset($advanced['enable'])) {

			// 'enable' can be a TS object, just as much as any other property
			if (isset($advanced['enable.'])) {
				$advanced['enable'] = $contentObject->cObjGetSingle($advanced['enable'], $advanced['enable.']);
				unset($advanced['enable.']);
			}
			if ((bool) $advanced['enable']) {

				$supportedKeys = array(
					'dateTimeAt',
					'dateAt',
					'dateFrom',
					'dateTo',
					'category',
					'subCategory',
					'tags'
				);
				foreach ($supportedKeys as $key) {
					$oKey = $key . '.';
					if (isset($advanced[$key][0]) && isset($advanced[$oKey]) && is_array($advanced[$oKey]) && !empty($advanced[$oKey])) {
						$advanced[$key] = $contentObject->cObjGetSingle($advanced[$key], $advanced[$oKey]);
						unset($advanced[$oKey]);
					}
				}

				try {
					$this->eventRepository
						->setCategory($advanced['category'])
						->setSubCategory($advanced['subCategory'])
						->setTags($advanced['tags']);

					// dateTimeAt has preference over dateAt
					$events = NULL;
					if (isset($advanced['dateTimeAt'][0])) {
						$dateTime = new \DateTime($advanced['dateTimeAt']);
						$events = $this->eventRepository->findAtDateTime($dateTime);
					} elseif (isset($advanced['dateAt'][0])) {
						$dateTime = new \DateTime($advanced['dateAt']);
						$events = $this->eventRepository->findAtDate($dateTime);
					} else {
						// .. or find between a range
						$dateStart = NULL;
						if (isset($advanced['dateFrom'][0])) {
							$dateStart = new \DateTime($advanced['dateFrom']);
						}
						$dateEnd = NULL;
						if (isset($advanced['dateTo'][0])) {
							$dateEnd = new \DateTime($advanced['dateTo']);
						}
						$events = $this->eventRepository->findBetweenDateTimeRange($dateStart, $dateEnd);
					}

					/* @var $eventService \Innologi\StreamovationsVp\Domain\Service\EventService */
					$eventService = $this->objectManager->get('Innologi\\StreamovationsVp\\Domain\\Service\\EventService');
					$events = $eventService->filterOutLiveStreams(
						$eventService->filterOutUnpublished($events)
					);

					if (isset($events[0])) {
						$arguments = array(
							'hash' => $events[0]->getEventId(),
							'__noRedirectOnException' => TRUE,
							'__noBackPid' => TRUE
						);
						$this->forward('show', NULL, NULL, $arguments);
					}

				} catch (HttpNotFound $e) {
					// no stream found returns a 404, which in this case really isn't an error
				}
			}
		}
	}

}
