<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers\Plugin;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Erigo\ErigoBase\Utility\TypoScriptUtility;

class FilterViewHelper extends AbstractViewHelper
{
	/** @var bool */
	protected $escapeOutput = false;
	
	public function initializeArguments(): void
	{
		$this->registerArgument('filters', 'array', 'Collection of chosen filters.', true, []);
		$this->registerArgument('filterValues', 'array', 'Currently used filters.', true, []);
		$this->registerArgument('contentObjectData', 'array', 'Data of cObject.', true, []);
		$this->registerArgument('request', Request::class, 'MVC request.', false, null);
		$this->registerArgument('extKey', 'string', 'Filter extension key.', false, null);
		$this->registerArgument('plugin', 'string', 'Filter plugin name.', false, null);
		$this->registerArgument('controller', 'string', 'Filter controller.', false, null);
		$this->registerArgument('action', 'string', 'Filter action.', false, null);
		$this->registerArgument('arguments', 'array', 'Filter controller arguments.', false, []);
		
		$this->registerArgument(
		    'additionalParams', 
		    'array', 
		    'Additional action URI query parameters that won\'t be prefixed '.
				'like $arguments (overrule $arguments) (only active if $actionUri is not set)', 
		    false, 
		    [],
	    );
		
		$this->registerArgument('settings', 'array', 'Plugin settings.', false, []);
		$this->registerArgument('fieldPrefix', 'string', 'Prefix to all fields.', false, null);
	}

	public function render(): mixed
	{
		$extKey = null;
		$plugin = null;
		$controller = null;
		
		$request = $this->arguments['request'];
		if ($request instanceof Request) {
			$extKey = $request->getControllerExtensionKey();
			$plugin = $request->getPluginName();
			$controller = $request->getControllerName();
		}
		
		$extKey = $this->arguments['extKey'] ?? $extKey;
		$plugin = $this->arguments['plugin'] ?? $plugin;
		$controller = $this->arguments['controller'] ?? $controller;
		
		if ($extKey == null || $plugin == null || $controller == null) {
			throw new \Exception(
			    'Extension key, plugin and controller must be specified. Just add "request" argument.',
		    );
		}

		$view = GeneralUtility::makeInstance(StandaloneView::class);
		
	    $defaultThemeName = TypoScriptUtility::THEME_DEFAULT;
		$currentThemeName = TypoScriptUtility::getFrontendTheme();
		
		$view->setTemplateRootPaths([
			'EXT:erigo_base/Resources/Private/Templates/Plugin/',
		]);
		
		$view->setPartialRootPaths([
			'EXT:erigo_base/Resources/Private/Partials/Plugin/',
			'EXT:'. $extKey .'/Resources/Private/Partials/',
			'EXT:'. $extKey .'/Resources/Private/Partials/'. $controller .'/',
			'fileadmin/themes/'. $defaultThemeName .'/view/Extensions/'. $extKey .'/Partials/',
			'fileadmin/themes/'. $defaultThemeName .'/view/Extensions/'. $extKey .'/Partials/'. $controller .'/',
			'fileadmin/themes/'. $currentThemeName .'/view/Extensions/'. $extKey .'/Partials/',
			'fileadmin/themes/'. $currentThemeName .'/view/Extensions/'. $extKey .'/Partials/'. $controller .'/',
		]);
		
		$view->setFormat('html');
		$view->setTemplate('Filter');
		$view->assignMultiple([
			'filters' => $this->arguments['filters'],
			'filterValues' => $this->arguments['filterValues'],
			'contentObjectData' => $this->arguments['contentObjectData'],
			'extensionName' => strtolower(str_replace('_', '', $extKey)),
			'pluginName' => $plugin,
			'controller' => $controller,
			'action' => $this->arguments['action'] ?? 'filter',
			'arguments' => $this->arguments['arguments'],
			'additionalParams' => $this->arguments['additionalParams'],
			'settings' => $this->arguments['settings'],
			'fieldPrefix' => $this->arguments['fieldPrefix'] ?? 'filter',
		]);
		
		return $view->render();
	}
}