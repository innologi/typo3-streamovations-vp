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
 * REST Magic Response
 *
 * This class is meant to be used only for proof-of-concepts.
 * It is a fallback once you disable the response-mapper via TS:
 * rest.features.disableResponseMapper = 1
 *
 * It allows you to use response objects in extbase and fluid
 * as if all their properties are defined and have getters and
 * setters. Imagine working with an unfinished or undocumented
 * REST api, and you just need a working concept. It sucks
 * having to create dozens of Domain Objects just for this
 * purpose, and possibly having to throw them away later on.
 *
 * The reason this is not meant for production-ready extensions,
 * is performance and lack of documented domain models.
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class MagicResponse implements ResponseInterface {
	// @TODO doc

	public function __construct(array $properties) {
		foreach ($properties as $property => $value) {
			$this->$property = $value;
		}
	}

	/**
	 * Dispatches magic get/set methods
	 *
	 * @param string $methodName The name of the magic method
	 * @param string $arguments The arguments of the magic method
	 * @return mixed
	 */
	public function __call($methodName, $arguments) {
		if (substr($methodName, 0, 3) === 'get' && strlen($methodName) > 4) {
			$propertyName = lcfirst(substr($methodName, 3));
			return isset($this->$propertyName)
				? $this->$propertyName
				: NULL;
		} elseif (substr($methodName, 0, 3) === 'set' && strlen($methodName) > 4) {
			if (!isset($arguments[0])) {
				// @TODO throw exception
			}
			$propertyName = lcfirst(substr($methodName, 3));
			$this->$propertyName = $arguments[0];
			return $this;
		}
		// @TODO throw exception
		//throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException('The method "' . $methodName . '" is not supported by the repository.', 1233180480);
	}

	public function __toString() {
		return serialize($this);
	}
}
