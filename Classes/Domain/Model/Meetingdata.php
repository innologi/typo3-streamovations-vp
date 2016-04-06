<?php
namespace Innologi\StreamovationsVp\Domain\Model;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use Innologi\StreamovationsVp\Library\RestRepository\ResponseAbstract;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
/**
 * Meetingdata Model
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Meetingdata extends ResponseAbstract {

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\StreamovationsVp\Domain\Model\Meetingdata\Topic>
	 */
	protected $topics;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\StreamovationsVp\Domain\Model\Meetingdata\Speaker>
	 */
	protected $speakers;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\StreamovationsVp\Domain\Model\Meetingdata\EventBreak>
	 */
	protected $eventBreaks;

	/**
	 * Although this would normally be:
	 * \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp>
	 * This property is converted to JSON in the Response Property Mapping
	 *
	 * @var string
	 */
	protected $topicTimeline;

	/**
	 * Although this would normally be:
	 * \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp>
	 * This property is converted to JSON in the Response Property Mapping
	 *
	 * @var string
	 */
	protected $speakerTimeline;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		$this->initStorageObjects();
	}

	/**
	 * Initializes all ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->topics = new ObjectStorage();
		$this->speakers = new ObjectStorage();
		$this->eventBreaks = new ObjectStorage();
		//$this->topicTimeline = new ObjectStorage();
		//$this->speakerTimeline = new ObjectStorage();
	}


	/**
	 * Returns topics
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getTopics() {
		return $this->topics;
	}

	/**
	 * Sets topics
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $topics
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata
	 */
	public function setTopics(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $topics) {
		$this->topics = $topics;
		return $this;
	}

	/**
	 * Adds a topic
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Topic $topic
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata
	 */
	public function addTopics(\Innologi\StreamovationsVp\Domain\Model\Meetingdata\Topic $topic) {
		$this->topics->attach($topic);
		return $this;
	}

	/**
	 * Remove a topic
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Topic $topic
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata
	 */
	public function removeTopics(\Innologi\StreamovationsVp\Domain\Model\Meetingdata\Topic $topic) {
		$this->topics->detach($topic);
		return $this;
	}

	/**
	 * Returns speakers
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getSpeakers() {
		return $this->speakers;
	}

	/**
	 * Sets speakers
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $speakers
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata
	 */
	public function setSpeakers(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $speakers) {
		$this->speakers = $speakers;
		return $this;
	}

	/**
	 * Adds a speaker
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Speaker $speaker
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata
	 */
	public function addSpeakers(\Innologi\StreamovationsVp\Domain\Model\Meetingdata\Speaker $speaker) {
		$this->speakers->attach($speaker);
		return $this;
	}

	/**
	 * Remove a speaker
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Speaker $speaker
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata
	 */
	public function removeSpeakers(\Innologi\StreamovationsVp\Domain\Model\Meetingdata\Speaker $speaker) {
		$this->speakers->detach($speaker);
		return $this;
	}

	/**
	 * Returns event breaks
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getEventBreaks() {
		return $this->eventBreaks;
	}

	/**
	 * Sets event breaks
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $eventBreaks
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata
	 */
	public function setEventBreaks(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $eventBreaks) {
		$this->eventBreaks = $eventBreaks;
		return $this;
	}

	/**
	 * Adds an event break
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Meetingdata\EventBreak $eventBreak
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata
	 */
	public function addEventBreaks(\Innologi\StreamovationsVp\Domain\Model\Meetingdata\EventBreak $eventBreak) {
		$this->eventBreaks->attach($eventBreak);
		return $this;
	}

	/**
	 * Remove an event break
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Meetingdata\EventBreak $eventBreak
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata
	 */
	public function removeEventBreaks(\Innologi\StreamovationsVp\Domain\Model\Meetingdata\EventBreak $eventBreak) {
		$this->eventBreaks->detach($eventBreak);
		return $this;
	}

	/**
	 * Returns topicTimeline
	 *
	 * @return string
	 */
	public function getTopicTimeline() {
		return $this->topicTimeline;
	}

	/**
	 * Sets topicTimeline
	 *
	 * @param string $topicTimeline
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata
	 */
	public function setTopicTimeline($topicTimeline) {
		$this->topicTimeline = $topicTimeline;
		return $this;
	}

	/**
	 * Adds a timestamp to topicTimeline
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp $timestamp
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata

	public function addTopicTimeline(\Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp $timestamp) {
		$this->topicTimeline->attach($timestamp);
		return $this;
	}*/

	/**
	 * Removes a timestamp from topicTimeline
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp $timestamp
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata

	public function removeTopicTimeline(\Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp $timestamp) {
		$this->topicTimeline->detach($timestamp);
		return $this;
	}*/

	/**
	 * Returns speakerTimeline
	 *
	 * @return string
	 */
	public function getSpeakerTimeline() {
		return $this->speakerTimeline;
	}

	/**
	 * Sets speakerTimeline
	 *
	 * @param string $speakerTimeline
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata
	 */
	public function setSpeakerTimeline($speakerTimeline) {
		$this->speakerTimeline = $speakerTimeline;
		return $this;
	}

	/**
	 * Adds a timestamp to speakerTimeline
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp $timestamp
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata

	public function addSpeakerTimeline(\Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp $timestamp) {
		$this->speakerTimeline->attach($timestamp);
		return $this;
	}*/

	/**
	 * Removes a timestamp from speakerTimeline
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp $timestamp
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata

	public function removeSpeakerTimeline(\Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp $timestamp) {
		$this->speakerTimeline->detach($timestamp);
		return $this;
	}*/

}
