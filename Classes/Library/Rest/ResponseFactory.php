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
 * REST Response Factory
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class ResponseFactory extends FactoryAbstract implements ResponseFactoryInterface {

	/**
	 * Create Response objects out of a raw response, and return them in an array
	 *
	 * @param string $rawResponse
	 * @param string $responseType
	 * @param string $objectType
	 * @return array
	 */
	public function createByRawResponse($rawResponse, $responseType, $objectType) {
		$responseArray = array();

		// @TODO pull this method apart?

		// convert response to array
		switch ($responseType) {
			case RequestInterface::RESPONSE_TYPE_JSON:
				$output = json_decode($rawResponse, TRUE);
				break;
			default:
				// @TODO add XML support
		}

		// @FIX remove this
		if (isset($output['result'])) {
			unset($output['result']);
		}

		// find objects
		if (isset($this->configuration['repository'][$objectType]['response']['container'])) {
			$mappingConfiguration = explode('.', $this->configuration['repository'][$objectType]['response']['container']);
			if ($mappingConfiguration[0] !== 'ROOT') {
				// @TODO throw exception
			}
			array_shift($mappingConfiguration);
			foreach ($mappingConfiguration as $key) {
				if (isset($output[$key])) {
					$output = $output[$key];
				} else {
					// @TODO throw RELEVANT exception
					throw new \Exception('blargh!');
				}
			}

			// create objects
			foreach ($output as $properties) {
				$responseArray[] = $this->create($properties, $objectType);
			}
		} else {
			$responseArray[] = $this->create($output, $objectType);
		}



		return $responseArray;
	}

	/**
	 * Create Response
	 *
	 * @param array $properties
	 * @param string $objectType
	 * @return ResponseInterface
	 */
	public function create(array $properties, $objectType) {
		// @LOW support property paths?
		// @TODO pull apart
		// property configuration
		if (isset($this->configuration['repository'][$objectType]['response']['property'])) {
			foreach ($this->configuration['repository'][$objectType]['response']['property'] as $property => $config) {
				if (isset($properties[$property])) {
					// map to new properties
					if (isset($config['mappings'])) {
						$mappings = $config['mappings'];
						$filteredValues = array();
						foreach ($mappings as $key => $mappingConfig) {
							if (!isset($mappingConfig['name'])) {
								// @TODO throw error
							}
							$name = $mappingConfig['name'];
							$value = isset($filteredValues[$key]) ? $filteredValues[$key] : $properties[$property];

							// condition, assumes property is a list
							if (isset($mappingConfig['if'])) {
								$if = $mappingConfig['if'];
								if (!isset($if['field']) || !isset($if['value'])) {
									// @TODO throw error
								}
								$ifField = $if['field'];
								$ifValue = $if['value'];

								$match = array();
								$remainder = array();
								foreach ($value as $element) {
									if (isset($element[$ifField]) && $element[$ifField] === $ifValue) {
										$match[] = $element;
									} else {
										$remainder[] = $element;
									}
								}
								$value = $match;

								if (isset($mappingConfig['else'])) {
									$else = $mappingConfig['else'];
									if (isset($else['sendToMapping'])) {
										$filteredValues[$else['sendToMapping']] = $remainder;
									}
								}
							}

							$properties[$name] = $value;
						}
					}

					// remove property
					if (isset($config['remove']) && (int)$config['remove'] === 1) {
						unset($properties[$property]);
						// anything else set in this config no longer matters
						continue;
					}

					// re-encode property to json
					if (isset($config['json']) && (int)$config['json'] === 1) {
						$properties[$property] = json_encode($properties[$property]);
					}
				}
			}
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
