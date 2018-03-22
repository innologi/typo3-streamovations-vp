<?php
namespace Innologi\StreamovationsVp\Library\RestRepository;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014-2017 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
	 * Prepares request headers
	 *
	 * TYPO3's use of guzzle expects $header => value format.
	 *
	 * @param array $headers
	 * @return void
	 */
	protected function initRequestHeaders(array $headers = array()) {
		$this->headers['Accept'] = $this->responseType === RequestInterface::RESPONSETYPE_JSON
			? 'application/json'
			: 'text/xml';

		if (!empty($headers)) {
			foreach ($headers as $header => $value) {
				$this->headers[$header] = is_array($value) ? join(',', $value) : $value;
			}
		}
	}

	/**
	 * Sends Request without use of cache, returns response
	 *
	 * @param boolean $returnRawResponse
	 * @return mixed
	 */
	public function sendNoCache($returnRawResponse = FALSE) {
		$error = [];
		$rawResponse = FALSE;

		/** @var \TYPO3\CMS\Core\Http\RequestFactory $requestFactory */
		$requestFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Http\RequestFactory::class);
		try {
			$response = $requestFactory->request(
				$this->requestUri->getRequestUri(),
				'GET',
				is_array($this->headers) ? ['headers' => $this->headers] : []
			);
			$rawResponse = $response->getBody()->getContents();

			if ($response->getStatusCode() >= 300 || empty($rawResponse)) {
				throw new \GuzzleHttp\Exception\TransferException(
					$response->getReasonPhrase(),
					$response->getStatusCode()
				);
			}
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			$error['code'] = $e->getCode();
			$error['message'] = $e->getMessage();
			$error['exception'] = $e;
		}

		if ($rawResponse === FALSE || !empty($error)) {
			$this->haltRequest($error, $rawResponse);
		}

		return $this->forceRawResponse || $returnRawResponse
			? $rawResponse
			: $this->mapResponseToObjects($rawResponse);
	}

}
