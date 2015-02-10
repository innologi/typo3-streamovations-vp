<?php
namespace Innologi\StreamovationsVp\Mvc\Controller;
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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Innologi\StreamovationsVp\Library\Rest\Exception\RestException;
use Innologi\StreamovationsVp\Exception\ErrorException;
/**
 * Video Controller
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Controller extends ActionController {

	/**
	 * Display nothing but flash messages. Only use it for forward()
	 *
	 * @return void
	 */
	public function noneAction() { }

	/**
	 * Calls the specified action method and passes the arguments.
	 *
	 * If the action returns a string, it is appended to the content in the
	 * response object. If the action doesn't return anything and a valid
	 * view exists, the view is rendered automatically.
	 *
	 * @return void
	 * @api
	 */
	protected function callActionMethod() {
		try {
			parent::callActionMethod();
		} catch (RestException $e) {
			$this->extensionErrorHandler($e);
		}/* catch (ErrorException $e) {
			$this->extensionErrorHandler($e);
		}*/
		// we don't want to interfere with Extbase's own exceptions
	}

	/**
	 * Handles any exception thrown by the extension.
	 *
	 * @param \Exception $e
	 * @return void
	 */
	protected function extensionErrorHandler(\Exception $e) {
		$this->clearCacheOnError();

		// @TODO llang
		$this->addFlashMessage(
			$e->getMessage(),
			'Foutmelding',
			FlashMessage::ERROR
		);

		// on showAction, redirect back to list if and only if not forwarded
		// from another action which explicitly tells us not to redirect
		if ($this->actionMethodName === 'showAction'
			&& $this->request->getInternalArgument('__noRedirectOnException') !== TRUE
		) {
			$backPid = isset($this->settings['backPid'][0])
				? $this->settings['backPid']
				: NULL;
			$this->redirect('list', NULL, NULL, NULL, $backPid);
		}

		$this->forward('none');
	}

}
