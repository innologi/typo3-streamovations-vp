<?php
namespace Innologi\StreamovationsVp\ViewHelpers\Collection;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Fluid\ViewHelpers\GroupedForViewHelper;
/**
 * collection.groupedForDateTime viewhelper
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class GroupedForDateTimeViewHelper extends GroupedForViewHelper {

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
	 * Groups the given array by the specified groupBy property.
	 *
	 * @param array $elements The array / traversable object to be grouped
	 * @param string $groupBy Group by this property
	 * @return array The grouped array in the form array('keys' => array('key1' => [key1value], 'key2' => [key2value], ...), 'values' => array('key1' => array([key1value] => [element1]), ...), ...)
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 */
	protected function groupElements(array $elements, $groupBy) {
		$groups = array('keys' => array(), 'values' => array());
		foreach ($elements as $key => $value) {
			if (is_array($value)) {
				$currentGroupIndex = isset($value[$groupBy]) ? $value[$groupBy] : NULL;
			} elseif (is_object($value)) {
				$currentGroupIndex = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getPropertyPath($value, $groupBy);
			} else {
				throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('GroupedForViewHelper only supports multi-dimensional arrays and objects', 1253120365);
			}

			//<-- @TODO finish cleaning up
			$format = $this->arguments['dateTimeFormat'];

			if (!$currentGroupIndex instanceof \DateTime) {
				if (is_numeric($currentGroupIndex)) {
					$temp = new \DateTime();
					$temp->setTimestamp($currentGroupIndex);
					$currentGroupIndex = $temp;
				} else {
					$currentGroupIndex = new \DateTime($currentGroupIndex);
				}
			}

			if (strpos($format, '%') !== FALSE) {
				$currentGroupIndex = strftime($format, $currentGroupIndex->format('U'));
			} else {
				$currentGroupIndex = $currentGroupIndex->format($format);
			}
			//-->

			$currentGroupKeyValue = $currentGroupIndex;
			if (is_object($currentGroupIndex)) {
				if ($currentGroupIndex instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy) {
					$currentGroupIndex = $currentGroupIndex->_loadRealInstance();
				}
				$currentGroupIndex = spl_object_hash($currentGroupIndex);
			}
			$groups['keys'][$currentGroupIndex] = $currentGroupKeyValue;
			$groups['values'][$currentGroupIndex][$key] = $value;
		}
		return $groups;
	}

}
