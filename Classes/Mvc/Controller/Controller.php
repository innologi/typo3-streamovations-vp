<?php
namespace Innologi\StreamovationsVp\Mvc\Controller;
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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\AbstractTemplateView;
use Innologi\StreamovationsVp\Library\RestRepository\Exception\{RestException, HttpNotFound, HttpForbidden, HostUnreachable, Configuration};
/**
 * Video Controller
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Controller extends ActionController {

	/**
	 * @var \Innologi\TYPO3AssetProvider\ProviderServiceInterface
	 */
	protected $assetProviderService;

	/**
	 *
	 * @param \Innologi\TYPO3AssetProvider\ProviderServiceInterface $assetProviderService
	 */
	public function injectAssetProviderService(\Innologi\TYPO3AssetProvider\ProviderServiceInterface $assetProviderService)
	{
		$this->assetProviderService = $assetProviderService;
	}

	/**
	 * Initializes the view before invoking an action method.
	 *
	 * Override this method to solve assign variables common for all actions
	 * or prepare the view in another way before the action is called.
	 *
	 * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view The view to be initialized
	 *
	 * @return void
	 * @api
	 */
	protected function initializeView(ViewInterface $view) {
		if ($view instanceof AbstractTemplateView && $this->request->getFormat() === 'html') {
			// provide assets as configured per action
			$this->assetProviderService->provideAssets(
				\strtolower($this->extensionName),
				$this->request->getControllerName(),
				$this->request->getControllerActionName()
			);
		}
	}

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
	 * @throws \Innologi\StreamovationsVp\Library\RestRepository\Exception\Configuration
	 * @return void
	 * @api
	 */
	protected function callActionMethod() {
		try {
			parent::callActionMethod();
		} catch (Configuration $e) {
			// pass these through
			throw $e;
		} catch (HttpNotFound $e) {
			$this->extensionErrorHandler(
				new \Exception(
					LocalizationUtility::translate('stream_n_a', $this->extensionName),
					$e->getCode(),
					$e
				)
			);
		} catch (HttpForbidden $e) {
			$this->extensionErrorHandler(
				new \Exception(
					LocalizationUtility::translate('api_403', $this->extensionName),
					$e->getCode(),
					$e
				)
			);
		} catch (HostUnreachable $e) {
			$this->extensionErrorHandler(
				new \Exception(
					LocalizationUtility::translate('host_n_a', $this->extensionName),
					$e->getCode(),
					$e
				)
			);
		} catch (RestException $e) {
			$this->extensionErrorHandler($e);
		}
		// we don't want to interfere with Extbase's own exceptions, so we can't catch \Exception
	}

	/**
	 * Handles any exception thrown by the extension.
	 *
	 * @param \Exception $e
	 * @return void
	 */
	protected function extensionErrorHandler(\Exception $e) {
		$this->clearCacheOnError();

		$this->addFlashMessage(
			$e->getMessage(),
			LocalizationUtility::translate('error_header', $this->extensionName),
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
