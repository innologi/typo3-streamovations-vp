<?php
namespace Innologi\StreamovationsVp\Domain\Model\Meetingdata;
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

/**
 * Timestamp Model
 *
 * This class is currently not used due to its containing property
 * being converted to JSON
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Timestamp extends MeetingdataAbstract {

	/**
	 * @var \DateTime
	 */
	protected $realtime;

	/**
	 * @var integer
	 */
	protected $relativeTime;

	/**
	 * @var integer
	 */
	protected $streamfileId;

	/**
	 * @var string
	 */
	protected $metadataType;

	/**
	 * @var string
	 */
	protected $timestampType;


	/**
	 * Returns realtime
	 *
	 * @return \DateTime
	 */
	public function getRealtime() {
		return $this->realtime;
	}

	/**
	 * Sets realtime
	 *
	 * @param \DateTime $realtime
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp
	 */
	public function setRealtime(\DateTime $realtime) {
		$this->realtime = $realtime;
		return $this;
	}

	/**
	 * Returns relativeTime
	 *
	 * @return integer
	 */
	public function getRelativeTime() {
		return $this->relativeTime;
	}

	/**
	 * Sets relativeTime
	 *
	 * @param integer $relativeTime
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp
	 */
	public function setRelativeTime($relativeTime) {
		$this->relativeTime = $relativeTime;
		return $this;
	}

	/**
	 * Returns streamfileId
	 *
	 * @return integer
	 */
	public function getStreamfileId() {
		return $this->streamfileId;
	}

	/**
	 * Sets streamfileId
	 *
	 * @param integer $streamfileId
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp
	 */
	public function setStreamfileId($streamfileId) {
		$this->streamfileId = $streamfileId;
		return $this;
	}

	/**
	 * Returns metadataType
	 *
	 * @return string
	 */
	public function getMetadataType() {
		return $this->metadataType;
	}

	/**
	 * Sets metadataType
	 *
	 * @param string $metadataType
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp
	 */
	public function setMetadataType($metadataType) {
		$this->metadataType = $metadataType;
		return $this;
	}

	/**
	 * Returns timestampType
	 *
	 * @return string
	 */
	public function getTimestampType() {
		return $this->timestampType;
	}

	/**
	 * Sets timestampType
	 *
	 * @param string $timestampType
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Timestamp
	 */
	public function setTimestampType($timestampType) {
		$this->timestampType = $timestampType;
		return $this;
	}

}
