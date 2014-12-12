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
class RequestFactory extends FactoryAbstract implements RequestFactoryInterface,\TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var string
	 */
	protected $httpConfKey = 'ignoreHttpConfiguration';

	/**
	 * Create REST request object
	 *
	 * @param string $objectType
	 * @return RequestInterface
	 */
	public function create($objectType) {
		return $this->objectManager->get(
			__NAMESPACE__ . '\\RequestInterface',
			$this->createRequestUriObject($objectType),
			$objectType,
			(isset($this->configuration['features'][$this->httpConfKey])
				&& $this->configuration['features'][$this->httpConfKey]
					? array()
					: $GLOBALS['TYPO3_CONF_VARS']['HTTP']
			)
		);
	}

	/**
	 * Creates RequestUri object for type
	 *
	 * @param string $type
	 * @return RequestUriInterface
	 */
	protected function createRequestUriObject($type = 'default') {
		$settings = array();
		$config = $this->configuration['repository'];

		if (isset($config[$type])) {
			$settings = $config[$type]['request'];
		}
		if ($type !== 'default' && isset($config['default']['request'])) {
			// merging of default settings with type-specific
			$settings = array_merge($config['default']['request'], $settings);
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
