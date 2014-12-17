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
				$dateEnd = NULL;
			}
			$events = $this->eventRepository->findBetweenDateTimeRange($dateTime, $dateEnd);
		}
		// @TODO error handling of lack of proper dates?

		$this->view->assign('events', $events);
	}

	/**
	 * Show playlist
	 *
	 * @param string $hash Playlist Hash
	 * @return void
	 */
	public function showAction($hash) {
		$playlist = $this->playlistRepository->findByHash($hash);
		if ($playlist) {
			// @TODO make meetingdata optional
			$meetingdata = $this->meetingdataRepository->findByHash($hash);
			$this->view->assign('meetingdata', $meetingdata);
		}
		$this->view->assign('playlist', $playlist);
	}

	/**
	 * Show playlist by configured hash
	 *
	 * @return void
	 */
	public function presetShowAction() {
		$arguments = array(
			'hash' => $this->settings['playlist']['hash']
		);
		$this->forward('show', NULL, NULL, $arguments);
	}

	/**
	 * Show LIVE stream
	 *
	 * @return void
	 */
	public function liveStreamAction() {
		$sessions = $this->eventRepository
			->setCategory($this->settings['session']['category'])
			->setSubCategory($this->settings['session']['subCategory'])
			->setTags($this->settings['session']['tags'])
			->findAtDateTime(new \DateTime());

		$arguments = array(
			'hash' => $sessions[0]
		);
		$this->forward('show', NULL, NULL, $arguments);
	}

}
