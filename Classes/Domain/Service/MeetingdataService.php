<?php
namespace Innologi\StreamovationsVp\Domain\Service;
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
use TYPO3\CMS\Core\SingletonInterface;
use Innologi\StreamovationsVp\Domain\Model\Meetingdata;
use Innologi\StreamovationsVp\Domain\Model\Meetingdata\EventBreak;
/**
 * Meetingdata Domain Service class
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class MeetingdataService implements SingletonInterface {

	/**
	 * Checks to see if the latest available EventBreak is active,
	 * if any at all.
	 *
	 * @param \Innologi\StreamovationsVp\Domain\Model\Meetingdata $meetingdata
	 * @return boolean
	 */
	public function isEventbreakActive(Meetingdata $meetingdata) {
		$eventBreaks = $meetingdata->getEventBreaks()->toArray();
		$eventBreak = end($eventBreaks);

		if ($eventBreak instanceof EventBreak && $eventBreak->getValid()) {
			$endTime = $eventBreak->getEnd();
			$now = time();
			if ($endTime === NULL || $endTime->getTimestamp() > $now) {
				if ($eventBreak->getStart()->getTimestamp() <= $now) {
					return TRUE;
				}
			}
		}

		return FALSE;
	}

}
