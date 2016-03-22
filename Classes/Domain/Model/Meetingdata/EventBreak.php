<?php
namespace Innologi\StreamovationsVp\Domain\Model\Meetingdata;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 * Event break Model
 *
 * This class is currently not used due to its containing property
 * being excluded in anything but the eid script.
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EventBreak extends ResponseAbstract {

	/**
	 * @var \DateTime
	 */
	protected $start;

	/**
	 * @var \DateTime
	 */
	protected $end;

	/**
	 * @var boolean
	 */
	protected $valid;

	/**
	 * Returns Start time
	 *
	 * @return \DateTime
	 */
	public function getStart() {
		return $this->start;
	}

	/**
	 * Sets Start time
	 *
	 * @param \DateTime $start
	 * @return $this
	 */
	public function setStart(\DateTime $start) {
		$this->start = $start;
		return $this;
	}

	/**
	 * Returns End time
	 *
	 * @return \DateTime
	 */
	public function getEnd() {
		return $this->end;
	}

	/**
	 * Sets End time
	 *
	 * @param \DateTime $end
	 * @return $this
	 */
	public function setEnd(\DateTime $end) {
		$this->end = $end;
		return $this;
	}

	/**
	 * Returns if eventbreak is valid
	 *
	 * @return boolean
	 */
	public function getValid() {
		return $this->valid;
	}

	/**
	 * Sets if eventbreak is valid
	 *
	 * @param $valid
	 * @return $this
	 */
	public function setValid($valid) {
		$this->valid = $valid;
		return $this;
	}

}
