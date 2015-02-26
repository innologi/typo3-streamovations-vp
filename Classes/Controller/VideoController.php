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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Innologi\StreamovationsVp\Mvc\Controller\Controller;
use Innologi\StreamovationsVp\Library\RestRepository\ResponseInterface;
use Innologi\StreamovationsVp\Library\RestRepository\Exception\HttpReturnedError;
use Innologi\StreamovationsVp\Library\RestRepository\Exception\UnexpectedResponseStructure;
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

			/* @var $eventService \Innologi\StreamovationsVp\Domain\Service\EventServiceInterface */
			$eventService = $this->objectManager->get('Innologi\\StreamovationsVp\\Domain\\Service\\EventServiceInterface');
			$events = $eventService->filterOutLiveStreams($events);

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
	 * @throws \Innologi\StreamovationsVp\Exception\UnexpectedResponseStructure
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
				// for jwPlayer we need to construct a valid configuration from the response
				// @see http://support.jwplayer.com/customer/portal/articles/1413113-configuration-options-reference
				if ($playerType === 1) {
					$useSmil = (int)$this->settings['jwPlayer']['smilSupport'] > 0;
					$ports = $playlist->getPorts();
					$application = $playlist->getApplication();
					$urlParts = array(
						0 => 'rtmp',
						1 => '://' . $playlist->getServer() . ':',
						2 => $ports['rtmp'],
						3 => '/' . $application . '/'
					);

					$playlistData = array(
						'playlist' => array(),
						'width' => $this->settings['jwPlayer']['width'],
						'height' => $this->settings['jwPlayer']['height'],
						// used by SVPS, not by jwplayer
						'application' => $application
					);

					$playlistItems = $playlist->getPlaylist();
					foreach ($playlistItems as $playlistItem) {
						if (!isset($playlistItem['source']['qualities'])) {
							throw new UnexpectedResponseStructure(
								LocalizationUtility::translate('unexpected_response_structure', $this->extensionName)
							);
						}

						$uP = $urlParts;
						// best case scenario: smil is available, provides quality selection
						if ($useSmil && isset($playlistItem['source']['smil'])) {
							$uP[0] = 'http';
							$uP[2] = $ports['http'];
							$source = isset($this->settings['jwPlayer']['smilTemplate'][0])
								? str_replace(
									'###SOURCE###',
									$playlistItem['source']['smil'],
									$this->settings['jwPlayer']['smilTemplate']
								)
								: $playlistItem['source']['smil'];

						// worst case scenario: no smil and I'm not bothering with creating quality selection
						} else {
							$source = isset($playlistItem['source']['defaultQuality']) && isset($playlistItem['source']['qualities'][$playlistItem['source']['defaultQuality']])
								? $playlistItem['source']['qualities'][$playlistItem['source']['defaultQuality']]
								: array_shift($playlistItem['source']['qualities']);
							// when livestreaming, $source is an array containing a stream for each available language
							if ($application === 'rtplive' && is_array($source)) {
								// livestream does not produce available languages, hence we use a configured csv list
								// @LOW are we sure the response does not produce a 'language' root-property during livestream?
								$languageFound = FALSE;
								$languages = GeneralUtility::trimExplode(',', $this->settings['live']['languages']);
								foreach ($languages as $lang) {
									if (isset($source[$lang])) {
										$source = $source[$lang];
										$languageFound = TRUE;
										break;
									}
								}
								// if configured language is not found, log the issue and just get the first element
								if (!$languageFound) {
									reset($source);
									GeneralUtility::devLog(
										sprintf(
											LocalizationUtility::translate('language_not_found', $this->extensionName),
											// tried languages
											$this->settings['live']['languages'],
											// used language (first one)
											key($source)
										),
										$this->extensionName,
										2
									);
									// use first language
									$source = array_shift($source);
								}
							}
						}

						$url = join('', $uP);
						$playlistData['playlist'][] = array(
							// @LOW 'image' => ''
							'sources' => array(
								0 => array(
									'file' => $url . $source
								)
							),
							// used by SVPS, not by jwplayer
							'streamfileId' => $playlistItem['streamfileId']
						);
					}
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
			// there is no need to 'filter out' VODs, because only LIVEstreams are active @ requested time (=now)
			$events = $this->eventRepository
				->setCategory($this->settings['event']['category'])
				->setSubCategory($this->settings['event']['subCategory'])
				->setTags($this->settings['event']['tags'])
				->findAtDateTime(new \DateTime());

			if (isset($events[0])) {
				$arguments = array(
					'hash' => $events[0]->getEventId(),
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

}
