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
use TYPO3\CMS\Extbase\Object\Exception\CannotReconstituteObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
/**
 * REST Response Mapper
 *
 * @package InnologiLibs
 * @subpackage RestRepository
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ResponseMapper implements ResponseMapperInterface, SingletonInterface {
	// @TODO add Extbase/FLOW validator-support

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
	 * @inject
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory
	 * @inject
	 */
	protected $dataMapFactory;

	/**
	 * Maps a response (as array) to an object of $objectType
	 *
	 * The objectType MUST implement ResponseInterface or an exception is thrown!
	 *
	 * @param string $objectType
	 * @param array $response
	 * @throws \TYPO3\CMS\Extbase\Object\Exception\CannotReconstituteObjectException
	 * @return ResponseInterface
	 */
	public function map(array $response, $objectType) {
		if (!in_array(__NAMESPACE__ . '\\ResponseInterface', class_implements($objectType))) {
			throw new CannotReconstituteObjectException('Cannot create empty instance of the class "' . $objectType . '" because it does not implement ' . __NAMESPACE__ . '\\ResponseInterface');
		}
		$object = $this->objectManager->getEmptyObject($objectType);
		$this->setObjectProperties($object, $response);
		return $object;
	}

	/**
	 * Sets all objectproperties with values taken from $responseData.
	 *
	 * The property types are determined by domain model variable annotations,
	 * as determined by the reflectionService
	 *
	 * @param ResponseInterface $object
	 * @param array $responseData
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException
	 * @return void
	 */
	protected function setObjectProperties(ResponseInterface $object, array $responseData) {
		$className = get_class($object);
		// class schema is necessary to determine variable annotations
		$classSchema = $this->reflectionService->getClassSchema($className);

		$properties = $object->_getProperties();
		foreach ($properties as $propertyName => $propertyValue) {
			$propertyData = $classSchema->getProperty($propertyName);
			if (isset($responseData[$propertyName]) && $responseData[$propertyName] !== NULL) {
				$propertyValue = $this->determinePropertyValue(
					$responseData[$propertyName],
					$propertyData['type'],
					$propertyData['elementType'],
					$propertyName,
					$className
				);

				// add value to object assuming a set{$PropertyName}() method exists, per convention
				$methodName = 'set' . ucfirst($propertyName);
				if (!method_exists($object, $methodName)) {
					throw new UnsupportedMethodException('The method "' . $methodName . '" is expected but missing in ' . $className);
				}
				$object->$methodName($propertyValue);
			}
		}
	}

	/**
	 * Determines property value as taken from $value and possibly converted per $propertyType,
	 * then returns the value.
	 *
	 * @param mixed $value Original response property value
	 * @param string $propertyType
	 * @param string $elementType Only used by ObjectStorage
	 * @param string $propertyName Only used by ObjectStorage/Exception
	 * @param string $className Only used by ObjectStorage/Exception
	 * @return mixed Determined by $propertyType
	 */
	protected function determinePropertyValue($value, $propertyType, $elementType, $propertyName, $className) {
		$propertyValue = NULL;

		switch ($propertyType) {
			case 'string':
				$propertyValue = (string) $value;
				break;
			case 'integer':
				$propertyValue = (int) $value;
				break;
			case 'float':
				$propertyValue = (double) $value;
				break;
			case 'boolean':
				$propertyValue = (bool) $value;
				break;
			case 'array':
				$this->throwExceptionIfValueIsNotArray($value, $propertyName, $className);
				// array, in this case, means just the original value
				$propertyValue = $value;
				break;
			case 'DateTime':
				$propertyValue = new \DateTime($value);
				break;
			case 'SplObjectStorage':
			case 'TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage':
				$propertyValue = $this->createObjectStorage($value, $elementType, $propertyName, $className);
				break;
			default:
				// if no matches, assume this is another object to be mapped
				$propertyValue = $this->map($value, $propertyType);
		}

		return $propertyValue;
	}

	/**
	 * Creates an ObjectStorage and attaches value elements mapped as
	 * objects of $elementType
	 *
	 * @param mixed $value
	 * @param string $elementType
	 * @param string $propertyName Only used by exceptionmessages
	 * @param string $className Only used by exceptionmessages
	 * @throws Exception\MissingElementType
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	protected function createObjectStorage($value, $elementType, $propertyName, $className) {
		if (!isset($elementType[0])) {
			throw new Exception\MissingElementType(
				$className . ' is missing an elementType definition for ObjectStorage "' . $propertyName . '"'
			);
		}

		// @LOW Dude. Just use objectManager so I can overrule ObjectStorage
		$propertyValue = new ObjectStorage();
		// in some cases, an EMPTY objectStorage is (boolean) FALSE in the REST Response
		if ($value !== FALSE) {
			$this->throwExceptionIfValueIsNotArray($value, $propertyName, $className);
			foreach ($value as $storageProperty) {
				$propertyValue->attach(
					$this->map($storageProperty, $elementType)
				);
			}
		}

		return $propertyValue;
	}


	/**
	 * Throws an exception if $value is not an array.
	 *
	 * This way, we get a helpful exception as opposed to the exception which
	 * occurs if we set $value to be an array in method constructor.
	 *
	 * @param mixed $value
	 * @param string $propertyName
	 * @param string $className
	 * @throws Exception\UnexpectedResponseStructure
	 * @return void
	 */
	protected function throwExceptionIfValueIsNotArray($value, $propertyName, $className) {
		if (!is_array($value)) {
			throw new Exception\UnexpectedResponseStructure(
				$className . ' expects "' . $propertyName . '" to be available in REST Response as array, instead is of type "' . gettype($value) . '"'
			);
		}
	}

}
