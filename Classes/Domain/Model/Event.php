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
/**
 * Event Model
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Event extends ResponseAbstract {

	/**
	 * Hash ID
	 *
	 * @var string
	 */
	protected $eventId;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var \DateTime
	 */
	protected $start;

	/**
	 * @var \DateTime
	 */
	protected $end;

	/**
	 * Unix timestamp of $start
	 *
	 * @var integer
	 */
	protected $startUnix;

	/**
	 * Unix timestamp of $end
	 *
	 * @var integer
	 */
	protected $endUnix;

	/**
	 * CSV
	 *
	 * @var string
	 */
	protected $tags;

	/**
	 * @var string
	 */
	protected $mainCategory;

	/**
	 * @var string
	 */
	protected $subCategory;

	/**
	 * @var string
	 */
	protected $combinedCategory;

	/**
	 * @var string
	 */
	protected $streamingType;



	/**
	 * Returns eventId
	 *
	 * @return string
	 */
	public function getEventId() {
		return $this->eventId;
	}

	/**
	 * Sets eventId
	 *
	 * @param string $eventId
	 * @return \Innologi\StreamovationsVp\Domain\Model\Event
	 */
	public function setEventId($eventId) {
		$this->eventId = $eventId;
		return $this;
	}

	/**
	 * Returns title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets title
	 *
	 * @param string $title
	 * @return \Innologi\StreamovationsVp\Domain\Model\Event
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Returns description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets description
	 *
	 * @param string $description
	 * @return \Innologi\StreamovationsVp\Domain\Model\Event
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * Returns start
	 *
	 * @return \DateTime
	 */
	public function getStart() {
		return $this->start;
	}

	/**
	 * Sets start
	 *
	 * @param \DateTime $start
	 * @return \Innologi\StreamovationsVp\Domain\Model\Event
	 */
	public function setStart(\DateTime $start) {
		$this->start = $start;
		return $this;
	}

	/**
	 * Returns end
	 *
	 * @return \DateTime
	 */
	public function getEnd() {
		return $this->end;
	}

	/**
	 * Sets end
	 *
	 * @param \DateTime $end
	 * @return \Innologi\StreamovationsVp\Domain\Model\Event
	 */
	public function setEnd(\DateTime $end) {
		$this->end = $end;
		return $this;
	}

	/**
	 * Returns startUnix
	 *
	 * @return integer
	 */
	public function getStartUnix() {
		return $this->startUnix;
	}

	/**
	 * Sets startUnix
	 *
	 * @param integer $startUnix
	 * @return \Innologi\StreamovationsVp\Domain\Model\Event
	 */
	public function setStartUnix($startUnix) {
		$this->startUnix = $startUnix;
		return $this;
	}

	/**
	 * Returns endUnix
	 *
	 * @return integer
	 */
	public function getEndUnix() {
		return $this->endUnix;
	}

	/**
	 * Sets endUnix
	 *
	 * @param integer $endUnix
	 * @return \Innologi\StreamovationsVp\Domain\Model\Event
	 */
	public function setEndUnix($endUnix) {
		$this->endUnix = $endUnix;
		return $this;
	}

	/**
	 * Returns tags
	 *
	 * @return string
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * Sets tags
	 *
	 * @param string $tags
	 * @return \Innologi\StreamovationsVp\Domain\Model\Event
	 */
	public function setTags($tags) {
		$this->tags = $tags;
		return $this;
	}

	/**
	 * Returns mainCategory
	 *
	 * @return string
	 */
	public function getMainCategory() {
		return $this->mainCategory;
	}

	/**
	 * Sets mainCategory
	 *
	 * @param string $mainCategory
	 * @return \Innologi\StreamovationsVp\Domain\Model\Event
	 */
	public function setMainCategory($mainCategory) {
		$this->mainCategory = $mainCategory;
		return $this;
	}

	/**
	 * Returns subCategory
	 *
	 * @return string
	 */
	public function getSubCategory() {
		return $this->subCategory;
	}

	/**
	 * Sets subCategory
	 *
	 * @param string $subCategory
	 * @return \Innologi\StreamovationsVp\Domain\Model\Event
	 */
	public function setSubCategory($subCategory) {
		$this->subCategory = $subCategory;
		return $this;
	}

	/**
	 * Returns combinedCategory
	 *
	 * @return string
	 */
	public function getCombinedCategory() {
		return $this->combinedCategory;
	}

	/**
	 * Sets combinedCategory
	 *
	 * @param string $combinedCategory
	 * @return \Innologi\StreamovationsVp\Domain\Model\Event
	 */
	public function setCombinedCategory($combinedCategory) {
		$this->combinedCategory = $combinedCategory;
		return $this;
	}


	/**
	 * Returns streamingType
	 *
	 * @return string
	 */
	public function getStreamingType() {
		return $this->streamingType;
	}

	/**
	 * Sets streamingType
	 *
	 * @param string $streamingType
	 * @return \Innologi\StreamovationsVp\Domain\Model\Event
	 */
	public function setStreamingType($streamingType) {
		$this->streamingType = $streamingType;
		return $this;
	}

}
