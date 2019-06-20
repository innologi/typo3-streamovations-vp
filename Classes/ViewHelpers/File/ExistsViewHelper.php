<?php
namespace Innologi\StreamovationsVp\ViewHelpers\File;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * file.exists viewhelper
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class ExistsViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * Initialize arguments
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('file', 'string', 'Filepath, which depending on if it exists, will trigger f:then or f:else.', TRUE);
	}

	// @TODO deprecated @ v9.0: https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Breaking-82414-RemoveCMSBaseViewHelperClasses.html
		// wanting to use the provided then/else logic, this will have to wait until the if VH has also been migrated
	/**
	 * Render method
	 *
	 * @return string
	 */
	public function render() {
		// return absolute filepath
		$file = GeneralUtility::getFileAbsFileName($this->arguments['file']);
		// is_file() adds the check if it's a file and not a directory
		if (is_file($file) && file_exists($file)) {
			return $this->renderThenChild();
		}
		return $this->renderElseChild();
	}

}
