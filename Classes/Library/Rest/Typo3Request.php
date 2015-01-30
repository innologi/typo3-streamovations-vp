<?php
namespace Innologi\StreamovationsVp\Library\Rest;

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
 * TYPO3 REST Request
 *
 * Utilizes api provided by TYPO3 CMS directly
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Typo3Request extends RequestAbstract implements RequestInterface {

	/**
	 * Sends Request, returns response
	 *
	 * @param boolean $returnRawResponse
	 * @return mixed
	 */
	public function send($returnRawResponse = FALSE) {
		$report = array();
		$rawResponse = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl(
			$this->requestUri->getRequestUri(), 0, $this->headers, $report
		);

		if ($rawResponse === FALSE || $report['error'] > 0) {
			$message = isset($report['message'][0]) ? $report['message'] : 'No response returned';
			// @TODO throw relevant exception
			throw new \Exception($message);
		}

		// @TODO what to return on errors?
		return $this->forceRawResponse || $returnRawResponse
			? $rawResponse
			: $this->mapResponseToObjects($rawResponse);
	}

}
