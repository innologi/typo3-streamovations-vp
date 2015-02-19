<?php
namespace Innologi\StreamovationsVp\Library\AssetProvider\Provider;
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

/**
 * Asset Provider Abstract
 *
 * Utilizes TYPO3 PageRenderer, ContentObject, TSFE and PageGenerator api
 *
 * @package InnologiLibs
 * @subpackage AssetProvider
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class ProviderAbstract implements ProviderInterface {

	/**
	 * @var \TYPO3\CMS\Core\Page\PageRenderer
	 */
	protected $pageRenderer;

	/**
	 * Provides some TS-processing functions utilized here
	 *
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $contentObject;

	/**
	 * Default asset configuration as utilized by PageRenderer
	 *
	 * @var array
	 */
	protected $defaultConfiguration = array();

	/**
	 * Class Constructer
	 *
	 * @return void
	 */
	public function __construct() {
		$this->initializeRenderers();
	}

	/**
	 * Initializes the necessary TYPO3 renderer classes used to process assets
	 *
	 * @return void
	 */
	protected function initializeRenderers() {
		// @LOW Extbase/FLOW api: $configurationManager->getContentObject();
		$this->contentObject = $GLOBALS['TSFE']->cObj;
		// @LOW pageRenderer is a singleton, so getting it via objectManager should get us the correct instance as well
		$this->pageRenderer = $GLOBALS['TSFE']->getPageRenderer();
	}

	/**
	 * Processes configuration of asset type
	 *
	 * @param array $configuration
	 * @param array $typoscript
	 * @return void
	 */
	public function processConfiguration(array $configuration, array $typoscript) {
		// process libs: generally (external) libraries
		if (isset($configuration['libs'])) {
			foreach ($configuration['libs'] as $key => $conf) {
				try {
					$conf = $this->convertConfig($conf);
					$conf = array_merge($this->defaultConfiguration, $conf);
					$this->addLibrary($conf, $key);
				} catch (Exception\ProviderException $e) {
					continue;
				}
			}
		}

		// process files: generally (internal) files
		if (isset($configuration['files'])) {
			foreach ($configuration['files'] as $key => $conf) {
				try {
					$conf = $this->convertConfig($conf);
					$conf = array_merge($this->defaultConfiguration, $conf);
					$this->addFile($conf, $key);
				} catch (Exception\ProviderException $e) {
					continue;
				}
			}
		}

		// process inline: generally small bits, or files to be processed through TypoScript
		if (isset($configuration['inline'])) {
			foreach ($configuration['inline'] as $key => $conf) {
				try {
					// treat inline configs as typoscript COA, so subitems can be TEMPLATE etc.
					$inline = trim(
						$this->contentObject->COBJ_ARRAY($typoscript['inline.'][$key . '.'])
					);
					if (isset($inline[0])) {
						$conf = array_merge($this->defaultConfiguration, $conf);
						$this->addInline($inline, $conf, $key);
					}
				} catch (Exception\ProviderException $e) {
					continue;
				}
			}
		}
	}

	/**
	 * Converts config for use by PageRenderer
	 *
	 * Adds filepath from node to $conf['file'] element.
	 *
	 * @param mixed $conf
	 * @return array
	 */
	protected function convertConfig($conf) {
		if (is_array($conf)) {
			$conf['file'] = $conf['_typoScriptNodeValue'];
		} else {
			$conf = array(
				'file' => $conf
			);
		}

		// @LOW for full if support, we actually need to provide $typoscript['if'], but then we have to add additional checks etc.
			// so who cares until we need to use isNull or any stdWrap on any of the if-properties
		// process typoscript if condition
		if (isset($conf['if']) && !$this->contentObject->checkIf($conf['if'])) {
			throw new Exception\FailedCondition('TypoScript if condition failed');
		}

		// if file is not external, resolve and validate (relative) filepath
		if (!isset($conf['external']) || !((bool) $conf['external'])) {
			$conf['file'] = $GLOBALS['TSFE']->tmpl->getFileName($conf['file']);
			if (!isset($conf['file'][0])) {
				throw new Exception\FileNotFound('File could not be found');
			}
		}

		// return converted configuration
		return $conf;
	}

}
