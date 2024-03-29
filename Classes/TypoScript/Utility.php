<?php
namespace Innologi\StreamovationsVp\TypoScript;
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

/**
 * TypoScript Utility
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Utility {

	/**
	 * Resolves prefixed paths, e.g. EXT:
	 *
	 * @param string $content
	 * @param array $conf
	 * @return string
	 */
	public function resolvePath($content, $conf) {
		$path = '';
		if (isset($conf['path'][0])) {
			/** @var \TYPO3\CMS\Frontend\Resource\FilePathSanitizer $filePathSanitizer */
			$filePathSanitizer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				\TYPO3\CMS\Frontend\Resource\FilePathSanitizer::class
			);
			$path = $filePathSanitizer->sanitize($conf['path']);
		}
		return $path;
	}

}
