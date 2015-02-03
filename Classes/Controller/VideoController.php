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
use Innologi\StreamovationsVp\Domain\Utility\EventUtility;
/**
 * Video Controller
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class VideoController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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
		// @LOW exclude livestream events? perhaps at response mapping?
		$this->eventRepository
			->setCategory($this->settings['event']['category'])
			->setSubCategory($this->settings['event']['subCategory'])
			->setTags($this->settings['event']['tags']);

		// @LOW this is really ugly. isn't there a cleaner way that doesn't involve multiple actions? or should we have multiple actions anyway?
		$events = NULL;
		$dateTime = new \DateTime();
		if (isset($this->settings['event']['dateAt'][0])) {
			// @FIX temp?
			$this->settings['event']['dateAt'] = strtotime($this->settings['event']['dateAt']);
			$dateTime->setTimestamp((int)$this->settings['event']['dateAt']);
			$events = $this->eventRepository->findAtDate($dateTime);
		} else {
			// @FIX temp?
			$this->settings['event']['dateFrom'] = strtotime($this->settings['event']['dateFrom']);
			$dateTime->setTimestamp((int)$this->settings['event']['dateFrom']);
			if (isset($this->settings['event']['dateTo'][0])) {
				$dateEnd = new \DateTime();
				// @FIX temp?
				$this->settings['event']['dateTo'] = strtotime($this->settings['event']['dateTo']);
				$dateEnd->setTimestamp((int)$this->settings['event']['dateTo']);
			} else {
				// @LOW should we set "now" as default value if dateFrom exists?
				$dateEnd = NULL;
			}
			$events = $this->eventRepository->findBetweenDateTimeRange($dateTime, $dateEnd);
		}
		// @TODO error handling of lack of proper dates?

		$events = EventUtility::filterOutStreamingType($events, 'live');
		$this->view->assign('events', $events);
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
			// @TODO make meetingdata optional
			$meetingdata = $this->meetingdataRepository->findByHash($hash);
			$this->view->assign('meetingdata', $meetingdata);
		}
		$this->view->assign('playlist', $playlist);

		// @LOW we should autodetect this once we allow livestreams via list
		$this->view->assign('isLiveStream', $isLiveStream);
		$this->view->assign('hash', $hash);
	}

	/**
	 * Show playlist by configured hash
	 *
	 * @return void
	 */
	public function presetShowAction() {
		if (isset($this->settings['playlist']['hash'][0])) {
			$arguments = array(
				'hash' => $this->settings['playlist']['hash']
			);
			$this->forward('show', NULL, NULL, $arguments);
		} else {
			// @TODO report no stream found on hash
		}
	}

	/**
	 * Show LIVE stream
	 *
	 * @return void
	 */
	public function liveStreamAction() {
		// there is no need to 'filter out' VODs, because only LIVEstreams are active @ requested time (=now)
		$events = $this->eventRepository
			->setCategory($this->settings['event']['category'])
			->setSubCategory($this->settings['event']['subCategory'])
			->setTags($this->settings['event']['tags'])
			->findAtDateTime(new \DateTime());

		if (isset($events[0])) {
			$arguments = array(
				// @TODO try/catch would be better
				'hash' => $events[0]->getEventId(),
				'isLiveStream' => TRUE
			);
			$this->forward('show', NULL, NULL, $arguments);
		} else {
			// @TODO report no livestream event
		}
	}

}
