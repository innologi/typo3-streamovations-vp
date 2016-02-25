<?php
namespace Innologi\StreamovationsVp\Controller;
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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Innologi\StreamovationsVp\Mvc\Controller\Controller;
use Innologi\StreamovationsVp\Library\RestRepository\ResponseInterface;
use Innologi\StreamovationsVp\Library\RestRepository\Exception\HttpReturnedError;
/**
 * Video Controller
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class VideoController extends Controller {

	/**
	 * @var \Innologi\StreamovationsVp\Domain\Repository\EventRepository
	 * @inject
	 */
	protected $eventRepository;

	/**
	 * @var \Innologi\StreamovationsVp\Domain\Repository\PlaylistRepository
	 * @inject
	 */
	protected $playlistRepository;

	/**
	 * @var \Innologi\StreamovationsVp\Domain\Repository\MeetingdataRepository
	 * @inject
	 */
	protected $meetingdataRepository;

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

		} catch(HttpReturnedError $e) {
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
		$playerType = (int)$this->settings['player'];

		// smvPlayer requires raw response
		if ($playerType === 2) {
			$this->playlistRepository->setForceRawResponse(TRUE);
		}

		$playlist = $this->playlistRepository->findByHash($hash);
		if ($playlist) {
			if ($playlist instanceof ResponseInterface) {
				$playlistData = NULL;

				/* @var $playlistService \Innologi\StreamovationsVp\Domain\Service\PlaylistService */
				$playlistService = $this->objectManager->get(
					'Innologi\\StreamovationsVp\\Domain\\Service\\PlaylistService',
					$this->extensionName
				);

				if ($playerType === 1) {
					// for jwPlayer we need to construct a valid configuration from the playlist-response
					$playlistData = $playlistService->createJwplayerSetup($playlist, $this->settings['jwPlayer']);
				}

				// javascript JSON.parse already deals with escaped slashes, but
				// still I found it inconvenient to have them when debugging, so..
				$playlist = version_compare(PHP_VERSION, '5.4', '<')
					? str_replace('\\/', '/', json_encode($playlistData))
					: json_encode($playlistData, JSON_UNESCAPED_SLASHES);
			}

			// at least one of meetingdata types must be enabled before getting anything
			$meetingdata = NULL;
			if ((isset($this->settings['topics']['enable']) && (bool)$this->settings['topics']['enable'])
				|| (isset($this->settings['speakers']['enable']) && (bool)$this->settings['speakers']['enable'])
			) {
				$meetingdata = $this->meetingdataRepository->findByHash($hash);
			}
			$this->view->assign('meetingdata', $meetingdata);
		}
		$this->view->assign('playlist', $playlist);

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
			// @FIX wait, eventRepo caches by default, doesn't it? Can we disable it here?
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
					'__noRedirectOnException' => TRUE,
					'__noBackPid' => TRUE
				);
				$this->forward('show', NULL, NULL, $arguments);
			}
		} catch (HttpReturnedError $e) {
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
						// no use continuing if neither format is available
						return;
					}

					/* @var $eventService \Innologi\StreamovationsVp\Domain\Service\EventService */
					$eventService = $this->objectManager->get('Innologi\\StreamovationsVp\\Domain\\Service\\EventService');
					$events = $eventService->filterOutLiveStreams(
						$eventService->filterOutUnpublished($events)
					);

					if (isset($events[0])) {
						$arguments = array(
							'hash' => $events[0]->getEventId(),
							// @TODO is this one relevant?
							'__noRedirectOnException' => TRUE,
							'__noBackPid' => TRUE
						);
						$this->forward('show', NULL, NULL, $arguments);
					}

				} catch (HttpReturnedError $e) {
					// no stream found returns a 404, which in this case really isn't an error
				}
			}
		}
	}

}
