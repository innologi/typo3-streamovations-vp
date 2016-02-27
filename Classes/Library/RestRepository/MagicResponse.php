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
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
/**
 * REST Magic Response
 *
 * This class is meant to be used only for proof-of-concepts.
 * It is a fallback once you disable the response-mapper via TS:
 * rest.repository.XXXXX.features.disableResponseMapper = 1
 *
 * It allows you to use response objects in extbase and fluid
 * as if all their properties are defined and have getters and
 * setters. Imagine working with an unfinished or undocumented
 * REST api, and you just need a working concept. It sucks
 * having to create dozens of Domain Objects just for this
 * purpose, and possibly having to throw them away later on.
 *
 * When a property is an array, the instance attempts to find out
 * if it should return an ObjectStorage or a MagicResponse object.
 * It will not return an array, ever. Because it cannot reliably
 * determine automagically whether the use-case expects an object
 * or an array, it implements several interfaces so that a
 * MagicResponse-object can be treated as an array instead.
 *
 * Although this class is a hack that goes against DDD-principles,
 * it might benefit performance in some specific use-cases.
 * Regardless, you should _always_ provide a mappable alternative
 * with descriptive domain models for production!
 *
 * @package InnologiLibs
 * @subpackage RestRepository
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MagicResponse extends ResponseAbstract implements \ArrayAccess, \Iterator, \Countable {

	/**
	 * An array representation of all the properties, necessary
	 * for some of the interfaces to work as expected.
	 *
	 * @var array
	 */
	protected $__properties = array();

	/**
	 * Constructor
	 *
	 * Sets properties as given by array as public properties.
	 *
	 * @param array $properties
	 * @return void
	 */
	public function __construct(array $properties) {
		$this->__properties = $properties;
		foreach ($properties as $property => $value) {
			$this->$property = $value;
		}

	}

	/**
	 * Dispatches magic get/set methods
	 *
	 * @param string $methodName The name of the magic method
	 * @param string $arguments The arguments of the magic method
	 * @throws \Exception
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException
	 * @return mixed
	 */
	public function __call($methodName, $arguments) {
		if (substr($methodName, 0, 3) === 'get' && strlen($methodName) > 4) {
			$property = NULL;
			$propertyName = $this->getPropertyName($methodName);
			if (isset($this->$propertyName)) {
				$property = $this->$propertyName;
				if (is_array($property)) {
					$property = $this->processArrayProperty($property);
				}
			}
			return $property;
		} elseif (substr($methodName, 0, 3) === 'set' && strlen($methodName) > 4) {
			if (!isset($arguments[0])) {
				throw new \Exception('Calling ' . $methodName . ' without an argument');
			}
			$propertyName = $this->getPropertyName($methodName);
			$this->offsetSet($propertyName, $arguments[0]);
			return $this;
		}
		throw new UnsupportedMethodException('The method "' . $methodName . '" is not supported');
	}

	/**
	 * Returns a hash map of property names and property values. Only for internal use.
	 *
	 * @return array The properties
	 */
	public function _getProperties() {
		$properties = parent::_getProperties();
		unset($properties['__properties']);
		return $properties;
	}

	/**
	 * Returns the property name derived from $methodName
	 *
	 * @param string $methodName
	 * @return string
	 */
	protected function getPropertyName($methodName) {
		return lcfirst(substr($methodName, 3));
	}

	/**
	 * Processes an array property by determining whether it should be
	 * an objectStorage or an object.
	 *
	 * @param array $array
	 * @return mixed
	 */
	protected function processArrayProperty(array $array) {
		// if $property is an array it can mean 1 of 3 things
		$property = current($array);
		$index = key($array);
		if (is_array($property) && is_int($index)) {
			// most likely, this is a list of objects, thus objectStorage
			$property = $this->createObjectStorage($array);
		} else {
			// either $property represents a value of an object or of a simple array
			// in which case we return a MagicResponse as it can be treated as both
			$property = new MagicResponse($array);
		}

		return $property;
	}

	/**
	 * Creates an objectStorage of MagicResponse objects out of an array-list
	 *
	 * @param array $listOfArrays
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	protected function createObjectStorage(array $listOfArrays) {
		$storage = new ObjectStorage();
		foreach ($listOfArrays as $properties) {
			$storage->attach(
				new MagicResponse($properties)
			);
		}
		return $storage;
	}



	/*
	 * The following methods are implementation classes of
	 * array-related interface classes. I cba to describe them,
	 * see the interfaces instead.
	 *
	 * Note that here, we don't check for array values.
	 * If you treat the object as an array, arrays don't get
	 * converted to MagicResponse objects.
	 */

	public function offsetExists($offset) {
		return isset($this->__properties[$offset]);
	}
	public function offsetGet($offset) {
		return $this->__properties[$offset];
	}
	public function offsetSet($offset, $value) {
		$this->$offset = $value;
		$this->__properties[$offset] = $value;
	}
	public function offsetUnset($offset) {
		unset($this->$offset);
		unset($this->__properties[$offset]);
	}

	public function current() {
		return current($this->__properties);
	}
	public function next() {
		return next($this->__properties);
	}
	public function key() {
		return key($this->__properties);
	}
	public function valid() {
		return current($this->__properties) !== FALSE;
	}
	public function rewind() {
		return reset($this->__properties);
	}

	public function count() {
		return count($this->__properties);
	}

}
