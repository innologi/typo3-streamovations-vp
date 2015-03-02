<?php
namespace Innologi\StreamovationsVp\Library\RestRepository;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Core\SingletonInterface;
/**
 * REST Response Configurator
 *
 * @package InnologiLibs
 * @subpackage RestRepository
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ResponseConfigurator implements ResponseConfiguratorInterface, SingletonInterface {

	/**
	 * Configures response data per response configuration
	 * and returns an altered $response.
	 *
	 * @param array $response
	 * @param array $responseConfiguration
	 * @return array
	 */
	public function configureResponse(array $response, array $responseConfiguration) {
		if (isset($responseConfiguration['rootElement'])) {
			// replace ROOT
			$response = $this->changeRootElement($response, $responseConfiguration['rootElement']);
		}

		// @LOW exclude becomes unnecessary once property.remove supports property-paths
		if (isset($responseConfiguration['exclude'])) {
			// exclude elements from result
			$response = $this->removeProperties($response, $responseConfiguration['exclude']);
		}

		return $response;
	}

	/**
	 * Alters response ROOT element
	 *
	 * RootElementPath may be given as a property path,
	 * e.g. event.attendee.address
	 *
	 * @param array $response
	 * @param string $rootElementPath
	 * @throws Exception\UnexpectedResponseStructure
	 * @return array
	 */
	protected function changeRootElement(array $response, $rootElementPath) {
		$rootElements = explode('.', $rootElementPath);
		foreach ($rootElements as $elem) {
			if (isset($response[$elem])) {
				$response = $response[$elem];
			} else {
				// unexpected structure, does not compute
				throw new Exception\UnexpectedResponseStructure(
					'Rest Repository Configuration error: root element "' . $rootElementPath . '" not available in REST response'
				);
			}
		}

		return $response;
	}

	/**
	 * Remove any matching element found in response.
	 *
	 * properties may be given as property paths,
	 * e.g. event.attendee.address
	 *
	 * @param array $response
	 * @param string $remove CSV
	 * @return array
	 */
	protected function removeProperties(array $response, $remove) {
		$lastReference = NULL;

		$removeProperties = explode(',', $remove);
		foreach ($removeProperties as $propertyPath) {
			$reference = &$response;
			$property = NULL;

			$properties = explode('.', $propertyPath);
			foreach ($properties as $property) {
				if (isset($reference[$property])) {
					// note that the $reference becomes the property value hence becomes useless.
					// we need $lastReference so we can still unset $property from it
					$lastReference = &$reference;
					$reference = &$reference[$property];
				} else {
					// note that no exception is thrown, we just move to the next
					continue 2;
				}
			}
			// coming here means the entire property path was found, while $reference contains
			// the property value. our business is with $lastReference instead.
			unset($lastReference[$property]);
		}

		return $response;
	}

	/**
	 * Configures properties per property configuration
	 * and returns an altered $properties.
	 *
	 * @param array $properties
	 * @param array $propertyConfiguration
	 * @param string $repositoryName
	 * @return array
	 */
	public function configureProperties(array $properties, array $propertyConfiguration, $repositoryName) {
		foreach ($propertyConfiguration as $property => $config) {
			if (isset($properties[$property])) {
				// map to new properties
				if (isset($config['mappings'])) {
					$properties = $this->mapProperties($properties, $config['mappings'], $property, $repositoryName);
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

		return $properties;
	}

	/**
	 * Maps properties per property mappings configuration
	 * and returns an altered $properties.
	 *
	 * @param array $properties
	 * @param array $mappings
	 * @param string $property
	 * @param string $repositoryName
	 * @throws Exception\Configuration
	 * @return array
	 */
	protected function mapProperties(array $properties, array $mappings, $property, $repositoryName) {
		// will contain sendToMapping-results from mapping conditions
		$filteredValues = array();

		foreach ($mappings as $key => $mappingConfig) {
			if (is_array($mappingConfig)) {
				if (!isset($mappingConfig['_typoScriptNodeValue'])) {
					throw new Exception\Configuration(
						'Rest Repository Configuration error: Missing node-value of response property mapping "' . $repositoryName . '.' . $property . '.' . $key . '"'
					);
				}
				$name = $mappingConfig['_typoScriptNodeValue'];
			} else {
				$name = $mappingConfig;
			}
			// $value receives filtered values if a sendToMapping id matches with $key
			$value = isset($filteredValues[$key]) ? $filteredValues[$key] : $properties[$property];

			// condition, assumes property is a list!
			if (isset($mappingConfig['if'])) {
				try {
					$value = $this->applyMappingCondition(
						$value,
						$mappingConfig['if'],
						isset($mappingConfig['else']) ? $mappingConfig['else'] : array(),
						$filteredValues
					);
				} catch (Exception\Configuration $e) {
					$e->setMessage(
						'Rest Repository Configuration error: ' . $e->getMessage() . ' "' . $repositoryName . '.' . $property . '.' . $key . '"'
					);
					throw $e;
				}
			}

			$properties[$name] = $value;
		}

		return $properties;
	}

	/**
	 * Applies a mapping condition set with an 'if' and optionally an 'else' configuration,
	 * and returns a changed/filtered $value.
	 * Assumes the property is always a list / array!
	 *
	 * If supports:
	 * - field = value-element fieldname
	 * - value = value-element value
	 *
	 * Else supports:
	 * - sendToMapping = mapping-id to send no-matches to
	 * Can only send to later mapping-id's!
	 *
	 * @param array $value
	 * @param array $if
	 * @param array $else
	 * @param array $filteredValues Is filled with no-matches if an else.sendToMapping is configured
	 * @throws Exception\Configuration
	 * @return array
	 */
	protected function applyMappingCondition(array $value, array $if, array $else = array(), array &$filteredValues = array()) {
		if (!isset($if['field']) || !isset($if['value'])) {
			throw new Exception\Configuration('Invalid if-configuration in response property mapping');
		}
		$ifField = $if['field'];
		$ifValue = $if['value'];

		// matches are set to $value
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

		if (!empty($else)) {
			// no-matches are set to $filteredValues per 'sendToMapping' configuration
			if (isset($else['sendToMapping'])) {
				$filteredValues[$else['sendToMapping']] = $remainder;
			}
		}

		return $value;
	}

}
