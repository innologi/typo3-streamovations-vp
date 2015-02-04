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
 * REST cURL Request
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class CurlRequest extends RequestAbstract implements RequestInterface {
	// @TODO finish implementation
	/**
	 * @var resource
	 */
	protected $resource;

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
		$this->resource = curl_init();

		if (!empty($httpConfiguration)) {
			$this->applyHttpConfiguration($httpConfiguration);
		}

		$this->setRequestOptions(
			array(
				CURLOPT_RETURNTRANSFER => TRUE,
				// @TODO which ones else?
				// CURLOPT_HEADER => TRUE/FALSE,
			)
		);
	}

	/**
	 * Destructor
	 *
	 * @return void
	 */
	public function __destruct() {
		curl_close($this->resource);
	}

	/**
	 * Sends Request, returns response
	 *
	 * @param boolean $returnRawResponse
	 * @return mixed
	 */
	public function send($returnRawResponse = FALSE) {
		// @TODO catch errors?
		$rawResponse = curl_exec($this->resource);
		return $rawResponse;
	}

	/**
	 * Applies HTTP configuration on cURL handle
	 *
	 * Typically, these are the settings that can be changed in
	 * the Install tool, read from  $GLOBALS['TYPO3_CONF_VARS']['HTTP']
	 *
	 * @param array $config
	 * @return void
	 */
	protected function applyHttpConfiguration(array $config) {
		$options = array();

		if (isset($config['proxy_host'][0])) {
			$options[CURLOPT_PROXY] = $config['proxy_host'];
			if (isset($config['proxy_port'][0])) {
				$options[CURLOPT_PROXYPORT] = $config['proxy_port'];
			}
			if (isset($config['proxy_user'][0])) {
				$options[CURLOPT_PROXYUSERPWD] = $config['proxy_user'];
				if (isset($config['proxy_password'][0])) {
					$options[CURLOPT_PROXYUSERPWD] .= ':' . $config['proxy_password'];
				}
			}
		}

		if (isset($config['userAgent'][0])) {
			$options[CURLOPT_USERAGENT] = $config['userAgent'];
		}

		// @TODO finish cURL system settings
		#$options[CURLOPT_FOLLOWLOCATION] = $config['follow_redirects'];
		#$options[CURLOPT_SSL_VERIFYPEER] = $config['ssl_verify_peer'];
		#$options[CURLOPT_SSL_VERIFYHOST] = $config['ssl_verify_host'];
		#$options[CURLOPT_CONNECTTIMEOUT] = $config['connect_timeout'];
		#$options[CURLOPT_TIMEOUT] = $config['timeout'];
		#$options[CURLOPT_PROXYAUTH] = $config['proxy_auth_scheme']; // CURLAUTH_BASIC CURLAUTH_NTLM
		#$options[CURLOPT_HTTP_VERSION] = $config['protocol_version']; // CURL_HTTP_VERSION_1_0 CURL_HTTP_VERSION_1_1
		#$options[CURLOPT_MAXREDIRS] = $config['max_redirects'];
		#$options[CURLOPT_CAINFO] = $config['ssl_cafile'];
		#$options[CURLOPT_CAPATH] = $config['ssl_capath'];
		#$options[CURLOPT_SSLKEY] = $config['ssl_local_cert'];
		#$options[CURLOPT_SSLKEYPASSWD] = $config['ssl_passphrase'];

		if (!empty($options)) {
			$this->setRequestOptions($options);
		}
	}

	/**
	 * Sets the request options on the cURL handle
	 *
	 * @param array $options
	 * @return void
	 */
	protected function setRequestOptions(array $options) {
		curl_setopt_array($this->resource, $options);
	}

}
