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
 * REST Request Interface
 *
 * @package InnologiLibs
 * @subpackage RestRepository
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface RequestInterface {

	const RESPONSETYPE_JSON = 0;
	const RESPONSETYPE_XML = 1;

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
	public function __construct($requestUri, $responseObjectType, array $headers = array(), array $cacheSettings = array(), $forceRawResponse = FALSE, array $httpConfiguration = array());

	/**
	 * Adds URL argument
	 *
	 * Proxy method for requestUri
	 *
	 * @param string $name
	 * @param string $value
	 * @return RequestInterface
	 */
	public function addArgument($name, $value);

	/**
	 * Sets Response type
	 *
	 * @param integer $responseType
	 * @return RequestInterface
	 */
	public function setResponseType($responseType);

	/**
	 * Sends Request, returns (cached) response
	 *
	 * @param boolean $returnRawResponse
	 * @return mixed
	 */
	public function send($returnRawResponse = FALSE);

	/**
	 * Sends Request without use of cache, returns response
	 *
	 * @param boolean $returnRawResponse
	 * @return mixed
	 */
	public function sendNoCache($returnRawResponse = FALSE);

}
