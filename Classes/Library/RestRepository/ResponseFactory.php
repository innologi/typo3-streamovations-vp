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
 * REST Response Factory
 *
 * @package InnologiLibs
 * @subpackage RestRepository
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ResponseFactory extends FactoryAbstract implements ResponseFactoryInterface {

	/**
	 * @var ResponseServiceInterface
	 */
	protected $responseService;

	/**
	 * Initializes the configuration
	 *
	 * @return void
	 */
	protected function initializeConfiguration() {
		parent::initializeConfiguration();
		$this->responseService = $this->objectManager->get(__NAMESPACE__ . '\\ResponseServiceInterface');
	}

	/**
	 * Create Response objects out of a raw response, and return them in an array
	 *
	 * @param string $rawResponse
	 * @param string $responseType
	 * @param string $objectType
	 * @throws Exception\UnexpectedResponseStructure
	 * @return mixed Array or object
	 */
	public function createByRawResponse($rawResponse, $responseType, $objectType) {
		// convert response to array
		switch ($responseType) {
			case RequestInterface::RESPONSETYPE_JSON:
				$response = json_decode($rawResponse, TRUE);
				break;
			default:
				// @TODO add XML support
		}

		// response configuration
		if (isset($this->configuration['repository'][$objectType]['response'])) {
			$response = $this->responseService->configureResponse(
				$response,
				$this->configuration['repository'][$objectType]['response']
			);

			// response-factory supports an additional configuration-property: list
			if (isset($this->configuration['repository'][$objectType]['response']['list'])
				&& (bool)$this->configuration['repository'][$objectType]['response']['list']
			) {
				// if list is set, treat the response root as an array of actual response elements
				$output = array();
				foreach ($response as $r) {
					$output[] = $this->create($r, $objectType);
				}
				return $output;
			}
		}

		$output = $this->create($response, $objectType);
		return $output;
	}

	/**
	 * Create Response
	 *
	 * @param array $properties
	 * @param string $objectType
	 * @return ResponseInterface
	 */
	public function create(array $properties, $objectType) {
		// property configuration
		if (isset($this->configuration['repository'][$objectType]['response']['property'])) {
			$properties = $this->responseService->configureProperties(
				$properties,
				$this->configuration['repository'][$objectType]['response']['property'],
				$objectType
			);
		}

		if (isset($this->configuration['features']['disableResponseMapper']) && (int)$this->configuration['features']['disableResponseMapper']) {
			// response mapper disabled: don't use this for production-ready extensions!
			/* @var $response ResponseInterface */
			$responseObject = $this->objectManager->get(__NAMESPACE__ . '\\ResponseInterface', $properties);
		} else {
			// @TODO finish this once we move on to actual model classes
		}

		return $responseObject;
	}

}
