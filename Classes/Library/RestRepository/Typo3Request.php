<?php
namespace Innologi\StreamovationsVp\Library\RestRepository;
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * TYPO3 REST Request
 *
 * Utilizes api provided by TYPO3 CMS directly
 *
 * @package InnologiLibs
 * @subpackage RestRepository
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Typo3Request extends RequestAbstract {

	/**
	 * Sends Request, returns response
	 *
	 * @param boolean $returnRawResponse
	 * @return mixed
	 */
	public function send($returnRawResponse = FALSE) {
		$data = array();
		$rawResponse = GeneralUtility::getUrl(
			$this->requestUri->getRequestUri(),
			0,
			$this->headers,
			$data
		);

		// unfortunately, Typo3Request doesn't always allow us to read the actual
		// response. instead, we get a status- and lib-based error message, e.g. 404
		// this is because of e.g. the forced CURLOPT_FAILONERROR = 1
		if ($rawResponse === FALSE || $data['error'] > 0) {
			$this->haltRequest($data, $rawResponse);
		}

		return $this->forceRawResponse || $returnRawResponse
			? $rawResponse
			: $this->mapResponseToObjects($rawResponse);
	}

}
