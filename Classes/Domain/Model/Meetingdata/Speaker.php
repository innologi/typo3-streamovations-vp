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
 * Speaker Model
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Speaker extends MeetingdataAbstract {

	/**
	 * @var string
	 */
	protected $firstname;

	/**
	 * @var string
	 */
	protected $lastname;

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var string
	 */
	protected $website;

	/**
	 * @var string
	 */
	protected $photo;


	/**
	 * Returns firstname
	 *
	 * @return string
	 */
	public function getFirstname() {
		return $this->firstname;
	}

	/**
	 * Sets firstname
	 *
	 * @param string $firstname
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Speaker
	 */
	public function setFirstname($firstname) {
		$this->firstname = $firstname;
		return $this;
	}

	/**
	 * Returns lastname
	 *
	 * @return string
	 */
	public function getLastname() {
		return $this->lastname;
	}

	/**
	 * Sets lastname
	 *
	 * @param string $lastname
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Speaker
	 */
	public function setLastname($lastname) {
		$this->lastname = $lastname;
		return $this;
	}

	/**
	 * Returns email
	 *
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Sets email
	 *
	 * @param string $email
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Speaker
	 */
	public function setEmail($email) {
		$this->email = $email;
		return $this;
	}

	/**
	 * Returns website
	 *
	 * @return string
	 */
	public function getWebsite() {
		return $this->website;
	}

	/**
	 * Sets website
	 *
	 * @param string $website
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Speaker
	 */
	public function setWebsite($website) {
		$this->website = $website;
		return $this;
	}

	/**
	 * Returns photo
	 *
	 * @return string
	 */
	public function getPhoto() {
		return $this->photo;
	}

	/**
	 * Sets photo
	 *
	 * @param string $photo
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata\Speaker
	 */
	public function setPhoto($photo) {
		$this->photo = $photo;
		return $this;
	}

}
