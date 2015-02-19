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

/**
 * REST Request URI
 *
 * @package InnologiLibs
 * @subpackage RestRepository
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RequestUri implements RequestUriInterface {

	/**
	 * Scheme-portion of the request URI, e.g. http or https
	 *
	 * @var string
	 */
	protected $scheme;

	/**
	 * Base-portion of the request URI
	 *
	 * @var string
	 */
	protected $baseUri;

	/**
	 * Api-portion of the request URI
	 *
	 * @var
	 */
	protected $apiUri;

	/**
	 * Arguments
	 *
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * Complete request URI
	 *
	 * @var string
	 */
	protected $requestUri;

	/**
	 * Signifies whether the Request URI was modified
	 *
	 * @var boolean
	 */
	protected $isModified = TRUE;

	/**
	 * Sets request scheme
	 *
	 * @param string $scheme
	 * @return RequestUriInterface
	 */
	public function setScheme($scheme) {
		$this->scheme = $this->processPropertyChange($scheme);
		return $this;
	}

	/**
	 * Returns request scheme
	 *
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}

	/**
	 * Sets request base URI
	 *
	 * @param string $baseUri
	 * @return RequestUriInterface
	 */
	public function setBaseUri($baseUri) {
		$this->baseUri = $this->processPropertyChange($baseUri);
		return $this;
	}

	/**
	 * Returns request base URI
	 *
	 * @return string
	 */
	public function getBaseUri() {
		return $this->baseUri;
	}

	/**
	 * Sets request api URI
	 *
	 * @param string $apiUri
	 * @return RequestUriInterface
	 */
	public function setApiUri($apiUri) {
		$this->apiUri = $this->processPropertyChange($apiUri);
		return $this;
	}

	/**
	 * Returns request api URI
	 *
	 * @return string
	 */
	public function getApiUri() {
		return $this->apiUri;
	}

	/**
	 * Adds URI argument
	 *
	 * @param string $name
	 * @param string $value
	 * @return RequestUriInterface
	 */
	public function addArgument($name, $value) {
		$name = $this->processPropertyChange($name);
		$this->arguments[$name] = $this->processPropertyChange($value);
		return $this;
	}

	/**
	 * Returns full request URI
	 *
	 * @return string
	 */
	public function getRequestUri() {
		if ($this->isModified) {
			$this->buildRequestUri();
			$this->isModified = FALSE;
		}
		return $this->requestUri;
	}

	/**
	 * Builds complete request URI from parts
	 *
	 * @return void
	 */
	protected function buildRequestUri() {
		$this->requestUri = $this->scheme . '://' .
			$this->baseUri . '/' .
			$this->apiUri . '/';
		foreach ($this->arguments as $parameter => $value) {
			// @LOW can't we speed this up with some kind of join? note the need for array keys
			$this->requestUri .= $parameter . '/' . $value . '/';
		}
	}

	/**
	 * Process a changed value and return any correction made
	 *
	 * @param string $value
	 * @return string
	 */
	protected function processPropertyChange($propertyValue) {
		$propertyValue = trim($propertyValue);
		if (!isset($propertyValue[0])) {
			// @TODO throw exception
		}
		$this->isModified = TRUE;
		return $propertyValue;
	}

}
