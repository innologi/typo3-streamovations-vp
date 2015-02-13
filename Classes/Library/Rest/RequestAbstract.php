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
 * Abstract REST Request
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
abstract class RequestAbstract {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var integer
	 */
	protected $responseType = RequestInterface::RESPONSETYPE_JSON;

	/**
	 * @var string
	 */
	protected $responseObjectType;

	/**
	 * @var boolean
	 */
	protected $forceRawResponse;

	/**
	 * @var array
	 */
	protected $headers = array();

	/**
	 * Request URI
	 *
	 * @var RequestUriInterface
	 */
	protected $requestUri;

	/**
	 * Constructor
	 *
	 * @param RequestUriInterface $requestUri
	 * @param string $responseObjectType
	 * @param boolean $forceRawResponse
	 * @param array $httpConfiguration
	 * @return void
	 */
	public function __construct($requestUri, $responseObjectType, $forceRawResponse = FALSE, array $httpConfiguration = array()) {
		$this->requestUri = $requestUri;
		$this->responseObjectType = $responseObjectType;
		$this->forceRawResponse = $forceRawResponse;

		$this->initRequestHeaders();
	}

	/**
	 * Adds URL argument
	 *
	 * Proxy method for requestUri
	 *
	 * @param string $name
	 * @param string $value
	 * @return RequestInterface
	 */
	public function addArgument($name, $value) {
		$this->requestUri->addArgument($name, $value);
		return $this;
	}

	/**
	 * Sets Response type
	 *
	 * @param integer $responseType
	 * @return RequestInterface
	 */
	public function setResponseType($responseType) {
		$this->responseType = $responseType;
		return $this;
	}

	/**
	 * Sends Request, returns response
	 *
	 * @param boolean $returnRawResponse
	 * @return void
	 */
	public function send($returnRawResponse = FALSE) {
		$rawResponse = 'Dummy Response';

		// implementation logic

		return $this->forceRawResponse || $returnRawResponse
			? $rawResponse
			: $this->mapResponseToObjects($rawResponse);
	}

	/**
	 * Halts request and throws exception.
	 *
	 * @param array $data
	 * @param string $response
	 * @throws Exception\HttpReturnedError
	 * @throws Exception\HostUnreachable
	 * @throws Exception\MalformedUrl
	 * @throws Exception\Request
	 * @return void
	 */
	protected function haltRequest(array $data, $response) {
		switch ($data['lib']) {
			case 'cURL':
				// @see http://curl.haxx.se/libcurl/c/libcurl-errors.html
				switch ($data['error']) {
					case 22:
						throw new Exception\HttpReturnedError($data['message']);
					case 6:
						throw new Exception\HostUnreachable($data['message']);
					case 3:
						throw new Exception\MalformedUrl($data['message']);
				}
			default:
				// @LOW log errormessage + request uri?
				throw new Exception\Request($data['message']);
		}
	}

	/**
	 * Prepares request headers
	 *
	 * @return void
	 */
	protected function initRequestHeaders() {
		$this->headers[] = 'Accept: ' . ($this->responseType === RequestInterface::RESPONSETYPE_JSON
			? 'application/json'
			: 'text/xml'
		);
	}

	/**
	 * Maps a raw response to objects and returns them in an array
	 *
	 * @param string $rawResponse
	 * @return array
	 */
	protected function mapResponseToObjects($rawResponse) {
		/* @var $responseFactory ResponseFactoryInterface */
		$responseFactory = $this->objectManager->get(__NAMESPACE__ . '\\ResponseFactoryInterface');

		return $responseFactory->createByRawResponse($rawResponse, $this->responseType, $this->responseObjectType);
	}

}
