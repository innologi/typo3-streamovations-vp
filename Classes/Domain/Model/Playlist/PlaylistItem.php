<?php
namespace Innologi\StreamovationsVp\Domain\Model\Playlist;
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
 * Playlist Item Model
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PlaylistItem extends ResponseAbstract {

	/**
	 * Server address
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * @var integer
	 */
	protected $playlistId;

	/**
	 * @var integer
	 */
	protected $streamfileId;

	/**
	 * @var \DateTime
	 */
	protected $startTime;

	/**
	 * @var \DateTime
	 */
	protected $stopTime;

	/**
	 * @var string
	 */
	protected $flags;

	/**
	 * @var \Innologi\StreamovationsVp\Domain\Model\Playlist\Source
	 */
	protected $source;


	/**
	 * Returns type
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets type
	 *
	 * @param string $type
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * Returns playlistId
	 *
	 * @return integer
	 */
	public function getPlaylistId() {
		return $this->playlistId;
	}

	/**
	 * Sets playlistId
	 *
	 * @param integer $playlistId
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem
	 */
	public function setPlaylistId($playlistId) {
		$this->playlistId = $playlistId;
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
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem
	 */
	public function setStreamfileId($streamfileId) {
		$this->streamfileId = $streamfileId;
		return $this;
	}

	/**
	 * Returns startTime
	 *
	 * @return \DateTime
	 */
	public function getStartTime() {
		return $this->startTime;
	}

	/**
	 * Sets startTime
	 *
	 * @param \DateTime $startTime
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem
	 */
	public function setStartTime(\DateTime $startTime) {
		$this->startTime = $startTime;
		return $this;
	}

	/**
	 * Returns stopTime
	 *
	 * @return \DateTime
	 */
	public function getStopTime() {
		return $this->stopTime;
	}

	/**
	 * Sets stopTime
	 *
	 * @param \DateTime $stopTime
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem
	 */
	public function setStopTime(\DateTime $stopTime) {
		$this->stopTime = $stopTime;
		return $this;
	}

	/**
	 * Returns flags
	 *
	 * @return string
	 */
	public function getFlags() {
		return $this->flags;
	}

	/**
	 * Sets flags
	 *
	 * @param string $flags
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem
	 */
	public function setFlags($flags) {
		$this->flags = $flags;
		return $this;
	}

	/**
	 * Returns source
	 *
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\Source
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * Sets source
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Playlist\Source $source
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem
	 */
	public function setSource(\Innologi\StreamovationsVp\Domain\Model\Playlist\Source $source) {
		$this->source = $source;
		return $this;
	}

}
