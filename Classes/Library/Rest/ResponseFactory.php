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
	 * @return mixed Array or object
	 */
	public function createByRawResponse($rawResponse, $responseType, $objectType) {
		$response = array();

		// @TODO pull this method apart?

		// convert response to array
		switch ($responseType) {
			case RequestInterface::RESPONSETYPE_JSON:
				$output = json_decode($rawResponse, TRUE);
				break;
			default:
				// @TODO add XML support
		}

		// @TODO pull apart
		if (isset($this->configuration['repository'][$objectType]['response'])) {
			$mappingConfiguration = $this->configuration['repository'][$objectType]['response'];

			// if set, will replace root with container element
			if (isset($mappingConfiguration['container'])) {
				// note that container may be set as path
				$containerKeys = explode('.', $mappingConfiguration['container']);
				foreach ($containerKeys as $key) {
					if (isset($output[$key])) {
						$output = $output[$key];
					} else {
						// unexpected structure, does not compute
						// @TODO throw RELEVANT exception
						throw new \Exception('Unexpected response-structure');
					}
				}
			}

			// exclude elements from result
			if (isset($mappingConfiguration['exclude'])) {
				// exclude is CSV
				$excludeKeys = explode(',', $mappingConfiguration['exclude']);
				// @LOW support paths?
				foreach ($excludeKeys as $key) {
					if (isset($output[$key])) {
						unset($output[$key]);
					}
					// note that nothing happens if key isn't found, no exception thrown
				}
			}

			// is output a list of response-objects?
			if (isset($mappingConfiguration['list']) && (bool)$mappingConfiguration['list']) {
				foreach ($output as $o) {
					$response[] = $this->create($o, $objectType);
				}
				return $response;
			}

		}

		$response = $this->create($output, $objectType);
		return $response;
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

					// filter list property
					if (isset($config['filterList']) && isset($properties[$property][0]) && is_array($properties[$property])) {
						switch ($config['filterList']) {
							// only get the last element
							case 'last':
								$properties[$property] = array(
									end($properties[$property])
								);
						}
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
