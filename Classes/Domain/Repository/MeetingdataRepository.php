<?php
namespace Innologi\StreamovationsVp\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 * Meetingdata Repository
 *
 * Meetingdata API:
 * [session] : required : alphanum
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class MeetingdataRepository extends \Innologi\StreamovationsVp\Library\Rest\Repository {

	/**
	 * Returns meetingdata associated with session hash.
	 *
	 * @param string $hash
	 * @return array
	 */
	public function findByHash($hash) {
		$response = $this->createRequest()
			->addArgument('session', $hash)
			->send();
		return $response;
	}

}
