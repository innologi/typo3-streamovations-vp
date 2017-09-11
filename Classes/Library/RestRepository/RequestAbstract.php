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
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * Abstract REST Request
 *
 * @package InnologiLibs
 * @subpackage RestRepository
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class RequestAbstract implements RequestInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
	 */
	protected $cache;

	/**
	 * @var boolean
	 */
	protected $cacheEnabled = FALSE;

	/**
	 * @var integer
	 */
	protected $cacheLifetime = NULL;

	/**
	 * @var boolean
	 */
	protected $cacheTags = FALSE;

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
	 * @param array $headers
	 * @param array $cacheSettings
	 * @param boolean $forceRawResponse
	 * @param array $httpConfiguration
	 * @return void
	 */
	public function __construct($requestUri, $responseObjectType, array $headers = array(), array $cacheSettings = array(), $forceRawResponse = FALSE, array $httpConfiguration = array()) {
		$this->requestUri = $requestUri;
		$this->responseObjectType = $responseObjectType;
		$this->forceRawResponse = $forceRawResponse;

		$this->initCaching($cacheSettings);
		$this->initRequestHeaders($headers);
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
	 * Sends Request, returns (cached) response.
	 *
	 * @param boolean $returnRawResponse
	 * @return mixed
	 */
	public function send($returnRawResponse = FALSE) {
		if (!$this->cacheEnabled) {
			return $this->sendNoCache($returnRawResponse);
		}

		$response = $this->getResponseFromCache($returnRawResponse);
		if ($response === NULL) {
			$response = $this->sendNoCache($returnRawResponse);
			$this->storeResponseInCache($response, $returnRawResponse);
		}
		return $response;
	}

	/**
	 * Returns a response from the cache, or NULL if none found
	 *
	 * @param boolean $findRawResponse
	 * @return mixed
	 */
	protected function getResponseFromCache($findRawResponse = FALSE) {
		$response = NULL;
		$entryIdentifier = $this->generateCacheEntryIdentifier($findRawResponse);
		if ($this->cache->has($entryIdentifier)) {
			$response = $this->cache->get($entryIdentifier);
		}
		return $response;
	}

	/**
	 * Stores a response in cache
	 *
	 * @param mixed $response
	 * @param boolean $isRawResponse
	 * @return void
	 */
	protected function storeResponseInCache($response, $isRawResponse = FALSE) {
		$tags = array();

		// only enable tags if requested by our cache settings, as these tags are wonderful for tracking
		// down caching issues, but are completely unused in and add overhead to production
		if ($this->cacheTags) {
			/** @var RepositorySettingsManagerInterface $repositorySettingsManager */
			$repositorySettingsManager = $this->objectManager->get(__NAMESPACE__ . '\\RepositorySettingsManagerInterface');
			$tags = array(
				$repositorySettingsManager->getRepositoryNameFromObjectType($this->responseObjectType),
				'lifetime_' . $this->cacheLifetime,
				'raw_' . (int) ($isRawResponse || $this->forceRawResponse),
				'type_' . $this->responseType
			);
		}

		$this->cache->set(
			$this->generateCacheEntryIdentifier($isRawResponse),
			$response,
			$tags,
			$this->cacheLifetime
		);
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
			case 'GuzzleHttp':
				if ($data['exception'] instanceof \GuzzleHttp\Exception\ClientException) {
					switch ($data['exception']->getCode()) {
						case 404:
							throw new Exception\HttpReturnedError($data['message']);
					}
				}
				break;
			case 'cURL':
				// @see http://curl.haxx.se/libcurl/c/libcurl-errors.html
				switch ($data['error']) {
					case 22:
						throw new Exception\HttpReturnedError($data['message']);
					case 7:
					case 6:
						throw new Exception\HostUnreachable($data['message']);
					case 3:
						throw new Exception\MalformedUrl($data['message']);
				}
		}
		// @LOW log errormessage + request uri?
		throw new Exception\Request($data['message']);
	}

	/**
	 * Prepares request headers
	 *
	 * Note that multi value headers should be given as an array.
	 * These will then be properly formatted as CSV.
	 *
	 * @param array $headers
	 * @return void
	 */
	protected function initRequestHeaders(array $headers = array()) {
		$this->headers[] = 'Accept: ' . ($this->responseType === RequestInterface::RESPONSETYPE_JSON
			? 'application/json'
			: 'text/xml'
		);

		if (!empty($headers)) {
			foreach ($headers as $header => $value) {
				$this->headers[] = $header . ': ' . (is_array($value) ? join(',', $value) : $value);
			}
		}
	}

	/**
	 * Initializes rest request cache
	 *
	 * Settings supported:
	 * - enable
	 * - lifetime
	 * - tags
	 *
	 * @param array $cacheSettings
	 * @return void
	 */
	protected function initCaching(array $cacheSettings) {
		$this->cacheEnabled = isset($cacheSettings['enable']) && (bool) $cacheSettings['enable'];
		if ($this->cacheEnabled) {
			$this->cacheLifetime = isset($cacheSettings['lifetime'])
				? (int) $cacheSettings['lifetime']
				: NULL;
			$this->cacheTags = isset($cacheSettings['tags']) && (bool) $cacheSettings['tags'];
			$this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('streamovations_vp_rest');
		}
	}

	/**
	 * A cache entry identifier is generated based on:
	 * - the request uri
	 * - if a raw response is requested / stored
	 * - cache lifetime
	 * - headers
	 *
	 * This way, each unique request will have its own cache entry, with the additional ability
	 * to differentiate between a cached raw response or a mapped response (default). Also, the
	 * cache lifetime is included to prevent a cache retrieval of the same uri for e.g. 1 hour
	 * to be retrieved for one that is set for e.g. 10 seconds.
	 *
	 * @param boolean $isRawResponse
	 * @return string
	 */
	protected function generateCacheEntryIdentifier($isRawResponse = FALSE) {
		return md5( join('---', [
			$this->requestUri->getRequestUri(),
			(int) ($isRawResponse || $this->forceRawResponse),
			$this->cacheLifetime,
			join(';', array_keys($this->headers)),
			join(';', $this->headers)
		]));
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
