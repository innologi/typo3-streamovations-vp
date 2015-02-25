<?php
namespace Innologi\StreamovationsVp\Library\RestRepository;
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
 * REST Response Service Interface
 *
 * @package InnologiLibs
 * @subpackage RestRepository
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface ResponseServiceInterface {

	/**
	 * Configures response data per response configuration
	 * and returns an altered $response.
	 *
	 * @param array $response
	 * @param array $responseConfiguration
	 * @return array
	 */
	public function configureResponse(array $response, array $responseConfiguration);

	/**
	 * Configures properties per property configuration
	 * and returns an altered $properties.
	 *
	 * @param array $properties
	 * @param array $propertyConfiguration
	 * @param string $objectType
	 * @return array
	 */
	public function configureProperties(array $properties, array $propertyConfiguration, $objectType);

}