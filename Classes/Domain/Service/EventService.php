<?php
namespace Innologi\StreamovationsVp\Domain\Service;
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
use TYPO3\CMS\Core\SingletonInterface;
/**
 * Event Domain Service class
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class EventService implements SingletonInterface {

	/**
	 * Constant for event->streamingType when event is a live-stream
	 * @var string
	 */
	const STREAMINGTYPE_LIVE = 'live';

	/**
	 * Filters out specified streamingType from $events.
	 *
	 * @param array $events
	 * @return array
	 */
	public function filterOutLiveStreams(array $events) {
		foreach ($events as $index => $event) {
			if ($event->getStreamingType() === self::STREAMINGTYPE_LIVE) {
				unset($events[$index]);
			}
		}
		return $events;
	}

}
