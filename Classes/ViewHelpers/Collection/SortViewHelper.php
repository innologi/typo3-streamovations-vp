<?php
namespace Innologi\StreamovationsVp\ViewHelpers\Collection;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
/**
 * container.sort viewhelper
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class SortViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeChildren = FALSE;

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * Initialize arguments
	 *
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('subject', 'array', 'Subject to sort, can be an array or objectStorage.', TRUE);
		$this->registerArgument('as', 'string', 'Name of sorted variable to be placed in template variable container.', TRUE);
		$this->registerArgument('order', 'string', 'ASC or DESC', FALSE, 'ASC');
		$this->registerArgument('sortBy', 'string', 'Sort by property value. If none given, sorts by key-index.');
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed
	 */
	public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
	{
		// first sort subject
		$subject = $arguments['subject'];
		$sortedSubject = NULL;
		if (is_array($subject) || $subject instanceof ObjectStorage) {
			$sortedSubject = static::sortCollection($subject, $arguments);
		}

		// then make sorted subject available in template __as array__
		$as = $arguments['as'];
		$renderingContext->getVariableProvider()->add($as, $sortedSubject);
		$output = $renderChildrenClosure();
		$renderingContext->getVariableProvider()->remove($as);

		return $output;
	}

	/**
	 * Sort collection
	 *
	 * @param mixed $subject
	 * @param array $arguments
	 * @return array
	 */
	protected static function sortCollection($subject, array $arguments) {
		// @LOW test this with ObjectStorage, cba to look if it implements Sortable until I need it
		$result = array();
		if (isset($arguments['sortBy'])) {
			foreach ($subject as $key => $value) {
				$key = static::getPropertyValue($value, $arguments['sortBy']);
				while (isset($result[$key])) {
					$key .= '_1';
				}
				$result[$key] = $value;
			}
		}
		switch ($arguments['order']) {
			case 'DESC':
				krsort($result);
				break;
			default:
				ksort($result);
		}

		return $result;
	}

	/**
	 * Gets the value to use as sorting value from $object
	 *
	 * @param object $object
	 * @param string $property
	 * @return mixed
	 */
	protected static function getPropertyValue($object, $property) {
		$value = ObjectAccess::getPropertyPath($object, $property);
		if ($value instanceof \DateTime) {
			$value = $value->getTimestamp();
		} elseif ($value instanceof ObjectStorage || is_array($value)) {
			// @LOW what if the value is a collection itself?
		}
		return $value;
	}

}
