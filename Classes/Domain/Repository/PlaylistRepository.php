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
 * Playlist Repository
 *
 * Playlist API:
 * [hash] : required : alphanum
 * [lang] : optional : ascending (default), descending, lang-eu, or 2 letter ISO-639-1 codes separated by colon
 * [qual] : optional : descending (default), ascending, or colon separated [broadcast, high, medium, low, minimal]
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class PlaylistRepository extends \Innologi\StreamovationsVp\Library\Rest\Repository {

	/**
	 * Returns playlist identified by hash.
	 *
	 * @param string $hash
	 * @param string $language
	 * @param string $quality
	 * @return array
	 */
	public function findByHash($hash, $language = NULL, $quality = NULL) {
		$request = $this->createRequest()->addArgument('hash', $hash);
		if ($language !== NULL) {
			$request->addArgument('lang', $language);
		}
		if ($quality !== NULL) {
			$request->addArgument('qual', $quality);
		}
		return $request->send();
	}

}
