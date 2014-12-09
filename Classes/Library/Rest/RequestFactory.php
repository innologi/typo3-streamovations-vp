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
 * REST Request Factory
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class RequestFactory implements RequestFactoryInterface,\TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * General REST request configuration
	 *
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var string
	 */
	protected $httpConfKey = 'applyHttpConfiguration';

	/**
	 * Class constructor
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function __construct(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		// it's this once, or DI + an if on each create(), or DI + an entire init on each create()
		$this->objectManager = $objectManager;
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
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
		$this->configuration = $frameworkConfiguration['rest'];
	}

	/**
	 * Create REST request object
	 *
	 * @param string $type Determines applied configuration
	 * @return Request
	 */
	public function create($type = 'default') {
		return $this->objectManager->get(
			__NAMESPACE__ . '\\RequestInterface',
			$this->createRequestUriObject($type),
			isset($this->configuration[$this->httpConfKey]) && $this->configuration[$this->httpConfKey]
				? $GLOBALS['TYPO3_CONF_VARS']['HTTP']
				: array()
		);
	}

	/**
	 * Creates RequestUri object for type
	 *
	 * @param string $type
	 * @return RequestUriInterface
	 */
	protected function createRequestUriObject($type) {
		$settings = array();
		$config = $this->configuration['repository'];

		if (isset($config[$type])) {
			$settings = $config[$type];
		}
		if ($type !== 'default' && isset($config['default'])) {
			// merging of default settings with type-specific
			$settings = array_merge($config['default'], $settings);
		}

		/* @var $requestUri RequestUriInterface */
		$requestUri = $this->objectManager->get(
			__NAMESPACE__ . '\\RequestUriInterface'
		);
		$requestUri
			// no isset checks here, if they don't exist errors occur anyway
			->setProtocol($settings['protocol'])
			->setBaseUri($settings['baseUri'])
			->setApiUri($settings['apiUri']);

		return $requestUri;
	}

}
