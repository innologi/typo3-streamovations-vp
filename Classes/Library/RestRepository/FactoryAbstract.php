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
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\SingletonInterface;
/**
 * Factory Abstract
 *
 * @package InnologiLibs
 * @subpackage RestRepository
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class FactoryAbstract implements SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var RepositoryMapperInterface
	 */
	protected $repositoryMapper;

	/**
	 * General REST configuration
	 *
	 * @var array
	 */
	protected $configuration;

	/**
	 * Merged REST configuration of default and objectType
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Class constructor
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function __construct(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		// because we want objectManager in __construct, we can't rely on DI as it is always later
		$this->objectManager = $objectManager;
		$this->repositoryMapper = $objectManager->get(__NAMESPACE__ . '\\RepositoryMapperInterface');
		$this->initializeConfiguration();
	}

	/**
	 * Initializes the configuration
	 *
	 * @return void
	 */
	protected function initializeConfiguration() {
		/* @var $configurationManager \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface */
		$configurationManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
		$frameworkConfiguration = $configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
		$this->configuration = $frameworkConfiguration['rest'];
	}

	/**
	 * Returns repository settings
	 *
	 * @param string $objectType
	 * @return array
	 */
	protected function getRepositorySettings($objectType) {
		$type = $this->repositoryMapper->getRepositoryNameFromObjectType($objectType);
		if (!isset($this->settings[$type])) {
			$this->settings[$type] = $this->mergeRepositorySettings($type);
		}
		return $this->settings[$type];
	}

	/**
	 * Merges default and type-specific settings
	 *
	 * @param string $type
	 * @return array
	 */
	protected function mergeRepositorySettings($type) {
		$config = $this->configuration['repository'];

		$settings = array();
		if (isset($config[$type])) {
			$settings = $config[$type];
		}
		if (isset($config['default'])) {
			// merging of default settings with type-specific
			$settings = array_replace_recursive($config['default'], $settings);
		}
		return $settings;
	}

}
