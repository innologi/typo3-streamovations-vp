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
use TYPO3\CMS\Core\SingletonInterface;
/**
 * REST Repository
 *
 * @package InnologiLibs
 * @subpackage RestRepository
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class Repository implements RepositoryInterface,SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $originalRequestParameters = array();

	/**
	 * Domain Object class name
	 *
	 * @var string
	 */
	protected $objectType;

	/**
	 * @var boolean
	 */
	protected $forceRawResponse = FALSE;

	/**
	 * Constructs a new Repository
	 *
	 * @return void
	 */
	public function __construct() {
		$this->initializeObjectType();
	}

	/**
	 * Sets original Request parameters
	 *
	 * @param \TYPO3\CMS\Extbase\Mvc\Request $originalRequest
	 * @return $this
	 */
	public function setOriginalRequestParameters(\TYPO3\CMS\Extbase\Mvc\Request $originalRequest) {
		$this->originalRequestParameters = array(
			'controller' => $originalRequest->getControllerName(),
			'action' => $originalRequest->getControllerActionName()
		);
		return $this;
	}

	/**
	 * If TRUE, forces RAW response from any request,
	 * regardless of the applied methods.
	 *
	 * @param boolean $forceRawResponse
	 * @return Repository
	 */
	public function setForceRawResponse($forceRawResponse) {
		$this->forceRawResponse = $forceRawResponse;
		return $this;
	}

	/**
	 * Initializes ObjectType from class name
	 *
	 * @return void
	 */
	protected function initializeObjectType() {
		$this->objectType = preg_replace(
			array('/\\\\Repository\\\\(?!.*\\\\Repository\\\\)/', '/Repository$/'),
			array('\\\\Model\\\\', ''), get_class($this)
		);
	}

	/**
	 * Creates a REST Request object
	 *
	 * @return RequestInterface
	 */
	protected function createRequest() {
		/* @var $requestFactory RequestFactoryInterface */
		$requestFactory = $this->objectManager->get(__NAMESPACE__ . '\\RequestFactoryInterface');

		return $requestFactory
			->reset($this->originalRequestParameters['controller'], $this->originalRequestParameters['action'])
			->create($this->objectType, $this->forceRawResponse);
	}

}
