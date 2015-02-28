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
 * Source Model
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Source extends ResponseAbstract {

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @var string
	 */
	protected $smil;

	/**
	 * @var boolean
	 */
	protected $multicast;

	/**
	 * @var string
	 */
	protected $multicastProxy;

	/**
	 * @var string
	 */
	protected $aspectRatio;

	/**
	 * @var array
	 */
	protected $qualities;

	/**
	 * @var string
	 */
	protected $defaultQuality;

	/**
	 * @var array
	 */
	protected $languages;


	/**
	 * Returns file
	 *
	 * @return string
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * Sets file
	 *
	 * @param string $file
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\Source
	 */
	public function setFile($file) {
		$this->file = $file;
		return $this;
	}

	/**
	 * Returns smil
	 *
	 * @return string
	 */
	public function getSmil() {
		return $this->smil;
	}

	/**
	 * Sets smil
	 *
	 * @param string $smil
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\Source
	 */
	public function setSmil($smil) {
		$this->smil = $smil;
		return $this;
	}

	/**
	 * Returns multicast
	 *
	 * @return boolean
	 */
	public function getMulticast() {
		return $this->multicast;
	}

	/**
	 * Sets multicast
	 *
	 * @param boolean $multicast
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\Source
	 */
	public function setMulticast($multicast) {
		$this->multicast = $multicast;
		return $this;
	}

	/**
	 * Returns multicastProxy
	 *
	 * @return string
	 */
	public function getMulticastProxy() {
		return $this->multicastProxy;
	}

	/**
	 * Sets multicastProxy
	 *
	 * @param string $multicastProxy
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\Source
	 */
	public function setMulticastProxy($multicastProxy) {
		$this->multicastProxy = $multicastProxy;
		return $this;
	}

	/**
	 * Returns aspectRatio
	 *
	 * @return string
	 */
	public function getAspectRatio() {
		return $this->aspectRatio;
	}

	/**
	 * Sets aspectRatio
	 *
	 * @param string $aspectRatio
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\Source
	 */
	public function setAspectRatio($aspectRatio) {
		$this->aspectRatio = $aspectRatio;
		return $this;
	}

	/**
	 * Returns qualities
	 *
	 * @return array
	 */
	public function getQualities() {
		return $this->qualities;
	}

	/**
	 * Sets qualities
	 *
	 * @param array $qualities
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\Source
	 */
	public function setQualities(array $qualities) {
		$this->qualities = $qualities;
		return $this;
	}

	/**
	 * Returns defaultQuality
	 *
	 * @return string
	 */
	public function getDefaultQuality() {
		return $this->defaultQuality;
	}

	/**
	 * Sets defaultQuality
	 *
	 * @param string $defaultQuality
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\Source
	 */
	public function setDefaultQuality($defaultQuality) {
		$this->defaultQuality = $defaultQuality;
		return $this;
	}

	/**
	 * Returns languages
	 *
	 * @return array
	 */
	public function getLanguages() {
		return $this->languages;
	}

	/**
	 * Sets languages
	 *
	 * @param array $languages
	 * @return \Innologi\StreamovationsVp\Domain\Model\Playlist\Source
	 */
	public function setLanguages(array $languages) {
		$this->languages = $languages;
		return $this;
	}

}
