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
use Innologi\StreamovationsVp\Library\RestRepository\ResponseAbstract;
/**
 * Meetingdata abstract
 *
 * Shared properties and methods of models foud in the meetingdata response.
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class MeetingdataAbstract extends ResponseAbstract {

	/**
	 * @var integer
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $title;


	/**
	 * Returns id
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Sets id
	 *
	 * @param integer $id
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata\MeetingdataAbstract
	 */
	public function setId($id) {
		$this->id = $id;
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
	 * @return \Innologi\StreamovationsVp\Domain\Model\Meetingdata\MeetingdataAbstract
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

}
