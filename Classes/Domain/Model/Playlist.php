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
 * Playlist Model
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Playlist extends ResponseAbstract {

	/**
	 * Server address
	 *
	 * @var string
	 */
	protected $server;

	/**
	 * Type of stream
	 *
	 * @var string
	 */
	protected $application;

	/**
	 * @var array
	 */
	protected $ports;

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem>
	 */
	protected $playlistItems;

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
		$this->playlistItems = new ObjectStorage();
	}


	/**
	 * Returns server
	 *
	 * @return string
	 */
	public function getServer() {
		return $this->server;
	}

	/**
	 * Sets server
	 *
	 * @param string $server
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist
	 */
	public function setServer($server) {
		$this->server = $server;
		return $this;
	}

	/**
	 * Returns application
	 *
	 * @return string
	 */
	public function getApplication() {
		return $this->application;
	}

	/**
	 * Sets application
	 *
	 * @param string $application
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist
	 */
	public function setApplication($application) {
		$this->application = $application;
		return $this;
	}

	/**
	 * Returns ports
	 *
	 * @return array
	 */
	public function getPorts() {
		return $this->ports;
	}

	/**
	 * Sets ports
	 *
	 * @param array $ports
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist
	 */
	public function setPorts(array $ports) {
		$this->ports = $ports;
		return $this;
	}

	/**
	 * Returns language
	 *
	 * @return string
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * Sets language
	 *
	 * @param string $language
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist
	 */
	public function setLanguage($language) {
		$this->language = $language;
		return $this;
	}

	/**
	 * Returns playlistItems
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getPlaylistItems() {
		return $this->playlistItems;
	}

	/**
	 * Sets playlistItems
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $playlistItems
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist
	 */
	public function setPlaylistItems(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $playlistItems) {
		$this->playlistItems = $playlistItems;
		return $this;
	}

	/**
	 * Adds a playlistItem to playlistItems
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem $playlistItem
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist
	 */
	public function addPlaylistItems(\Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem $playlistItem) {
		$this->playlistItems->attach($playlistItem);
		return $this;
	}

	/**
	 * Removes a playlistItem from playlistItems
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem $playlistItem
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist
	 */
	public function removePlaylistItems(\Innologi\StreamovationsVp\Domain\Model\Playlist\PlaylistItem $playlistItem) {
		$this->playlistItems->detach($playlistItem);
		return $this;
	}

}
