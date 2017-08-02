<?php
namespace Innologi\StreamovationsVp\ViewHelpers\Collection;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015-2017 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3Fluid\Fluid\ViewHelpers\GroupedForViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
/**
 * collection.groupedForDateTime viewhelper
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class GroupedForDateTimeViewHelper extends GroupedForViewHelper {

	/**
	 * @var string
	 */
	protected static $dateTimeFormat;

	/**
	 * @var boolean
	 */
	protected $escapeChildren = FALSE;

	/**
	 * Initialize arguments
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('dateTimeFormat', 'string', 'DateTime format to base grouping on.', TRUE);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed
	 */
	public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
	{
		static::$dateTimeFormat = $arguments['dateTimeFormat'];
		return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
	}

	/**
	 * Groups the given array by the specified groupBy property.
	 *
	 * @param array $elements The array / traversable object to be grouped
	 * @param string $groupBy Group by this property
	 * @return array The grouped array in the form array('keys' => array('key1' => [key1value], 'key2' => [key2value], ...), 'values' => array('key1' => array([key1value] => [element1]), ...), ...)
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 */
	protected static function groupElements(array $elements, $groupBy) {
		$groups = ['keys' => [], 'values' => []];
		foreach ($elements as $key => $value) {
			if (is_array($value)) {
				$currentGroupIndex = isset($value[$groupBy]) ? $value[$groupBy] : NULL;
			} elseif (is_object($value)) {
				$currentGroupIndex = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getPropertyPath($value, $groupBy);
			} else {
				throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('GroupedForViewHelper only supports multi-dimensional arrays and objects', 1253120365);
			}

			if (!$currentGroupIndex instanceof \DateTime) {
				if (is_numeric($currentGroupIndex)) {
					$temp = new \DateTime();
					$temp->setTimestamp($currentGroupIndex);
					$currentGroupIndex = $temp;
				} else {
					$currentGroupIndex = new \DateTime($currentGroupIndex);
				}
			}
			$currentGroupIndex = $currentGroupIndex->format(static::$dateTimeFormat);

			$currentGroupKeyValue = $currentGroupIndex;
			$groups['keys'][$currentGroupIndex] = $currentGroupKeyValue;
			$groups['values'][$currentGroupIndex][$key] = $value;
		}
		return $groups;
	}

}
