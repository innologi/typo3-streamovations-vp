<?php
namespace Innologi\StreamovationsVp\Library\RestRepository;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
/**
 * REST Repository Settings Manager
 *
 * @package InnologiLibs
 * @subpackage RestRepository
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RepositorySettingsManager implements RepositorySettingsManagerInterface,SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Unprocessed REST configuration
	 *
	 * @var array
	 */
	protected $rawConfiguration;

	/**
	 * Merged REST top-level configuration per context
	 *
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * Merged REST repository-level settings per context
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var array
	 */
	protected $repositoryNames = array();

	/**
	 * @var string
	 */
	protected $controller;

	/**
	 * @var string
	 */
	protected $action;

	/**
	 * {@inheritDoc}
	 * @see RepositorySettingsManagerInterface::setContext()
	 */
	public function setContext($controller = NULL, $action = NULL) {
		$this->controller = $controller;
		$this->action = $action;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see RepositorySettingsManagerInterface::getRepositoryNameFromObjectType()
	 */
	public function getRepositoryNameFromObjectType($objectType) {
		if (!isset($this->repositoryNames[$objectType])) {
			$repositoryName = $objectType;
			// remove namespace
			if ( ($pos = strrpos($objectType, '\\')) !== FALSE ) {
				$repositoryName = substr($objectType, ($pos + 1));
			}
			$this->repositoryNames[$objectType] = $repositoryName;
		}
		return $this->repositoryNames[$objectType];
	}

	/**
	 * {@inheritDoc}
	 * @see RepositorySettingsManagerInterface::getSettings()
	 */
	public function getSettings($objectType) {
		$hash = md5($objectType . '---' . $this->controller . '---' . $this->action);
		if (!isset($this->settings[$hash])) {
			$this->settings[$hash] = $this->mergeRepositorySettings($objectType);
		}
		return $this->settings[$hash];
	}

	/**
	 * Merge repository settings and return it
	 *
	 * @param string $objectType
	 * @return array
	 */
	protected function mergeRepositorySettings($objectType) {
		$configuration = $this->getConfiguration();
		$repository = $this->getRepositoryNameFromObjectType($objectType);

		$settings = array();
		if (isset($configuration[$repository])) {
			$settings = $configuration[$repository];
		}
		if (isset($configuration['default'])) {
			// merging of default settings with type-specific
			$settings = array_replace_recursive($configuration['default'], $settings);
		}
		return $settings;
	}

	/**
	 * Return context configuration
	 *
	 * @return array
	 */
	protected function getConfiguration() {
		$hash = md5($this->controller . '---' . $this->action);
		if (!isset($this->configuration[$hash])) {
			$this->configuration[$hash] = $this->mergeContextConfiguration();
		}
		return $this->configuration[$hash];
	}

	/**
	 * Merge context configuration and return it
	 *
	 * @return array
	 */
	protected function mergeContextConfiguration() {
		$configuration = $this->getRawConfiguration();
		if ( !($this->controller === NULL || $this->action === NULL)
			&& isset($configuration['controller'][$this->controller]['action'][$this->action])
		) {
			return array_replace_recursive(
				$configuration['repository'],
				$configuration['controller'][$this->controller]['action'][$this->action]['repository']
			);
		}
		return $configuration['repository'];
	}

	/**
	 * Return unprocessed configuration
	 *
	 * @return array
	 */
	protected function getRawConfiguration() {
		if (!isset($this->rawConfiguration)) {
			/* @var $configurationManager ConfigurationManagerInterface */
			$configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
			$frameworkConfiguration = $configurationManager->getConfiguration(
				ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
			);
			$this->rawConfiguration = $frameworkConfiguration['rest'];
		}
		return $this->rawConfiguration;
	}

}
