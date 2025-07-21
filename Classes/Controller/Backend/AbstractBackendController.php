<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Configuration\TranslationConfigurationProvider;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\DocHeaderComponent;
use TYPO3\CMS\Backend\Template\{ModuleTemplate, ModuleTemplateFactory};
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use Erigo\ErigoBase\Controller\AbstractController;
use Erigo\ErigoBase\Utility\TcaUtility;

abstract class AbstractBackendController extends AbstractController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected ModuleTemplate $moduleTemplate;
    protected PageRenderer $pageRenderer;
	protected IconFactory $iconFactory;

	public function injectModuleTemplateFactory(ModuleTemplateFactory $moduleTemplateFactory): void
	{
	    $this->moduleTemplateFactory = $moduleTemplateFactory;
	}

	public function injectPageRenderer(PageRenderer $pageRenderer): void
	{
	    $this->pageRenderer = $pageRenderer;
	}

	public function injectIconFactory(IconFactory $iconFactory): void
	{
	    $this->iconFactory = $iconFactory;
	}
	
	/**
	 * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::initializeAction()
	 */
	protected function initializeAction(): void
	{
	    $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
	}

	/**
	 * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::resolveActionMethodName()
	 */
	protected function resolveActionMethodName(): string
	{
		$action = $this->getRequestParam('action') ?? $this->getDefaultActionName();
	
		return $action .'Action';
	}
	
	protected function translate(string $key, string $extKey = null, string $fileSuffix = null): ?string
	{
		if ($extKey === null) {
			$extKey = GeneralUtility::camelCaseToLowerCaseUnderscored($this->getRealExtensionName());
		}
		
		if ($fileSuffix === null) {
			$moduleName = $this->getModuleName();
			$moduleName = str_replace($this->getRealExtensionName(), '', $moduleName);
			$moduleName = str_replace('_', '', $moduleName);
			$moduleName = GeneralUtility::camelCaseToLowerCaseUnderscored($moduleName);
			
			$fileSuffix = 'mod_'. $moduleName;
		}
		
		return $this->translateString(TcaUtility::getLabel($key, $extKey, $fileSuffix));
	}
	
	protected function translateString(string $string): ?string
	{
		if (substr($string, 0, 4) == 'LLL:') {
			return $this->getLanguageService()->sL($string);
		}
		
		return $string;
	}
	
	protected function getModuleName(): string
	{
		$moduleName = $this->getRequestParam('route');
		$moduleName = str_replace('/', '_', trim($moduleName, '/'));
		
		$moduleNameParts = explode('_', $moduleName, 3);
		
		return $moduleNameParts[0] .'_'. $moduleNameParts[1];
	}
	
	protected function getUserModuleData(string $action = null, string $key = null): mixed
	{
		$moduleData = $this->getUserAuth()->getModuleData($this->getModuleName()) ?: [];
		
		if ($action == null) {
			return $moduleData;
		}
		
		if (!array_key_exists($action, $moduleData)) {
			$moduleData[$action] = [];
		}
		
		if ($key == null) {
			return $moduleData[$action];
		}
		
		if (array_key_exists($key, $moduleData[$action])) {
			return $moduleData[$action][$key];
		}
		
		return null;
	}
	
	protected function setUserModuleData(string $action, string $key, mixed $value = null): void
	{
		$moduleData = $this->getUserModuleData();
		
		if (!array_key_exists($action, $moduleData)) {
			$moduleData[$action] = [];
		}
		
		if ($value === null) {
			if (array_key_exists($key, $moduleData[$action])) {
				unset($moduleData[$action][$key]);
			}
			
		} else {
			$moduleData[$action][$key] = $value;
		}
		
		$this->getUserAuth()->pushModuleData($this->getModuleName(), $moduleData);
	}
	
	/**
	 * Přidání JS modulů - aktualizováno pro TYPO3 v13
	 * V TYPO3 v13 je RequireJS deprecated ve prospěch ES6 modulů
	 */
	protected function addJsModules(array $jsModules): void
	{
		$pageRenderer = $this->getPageRenderer();
		
		foreach ($jsModules as $jsModule) {
		    // V TYPO3 v13 se doporučuje používat ES6 moduly místo RequireJS
		    if (method_exists($pageRenderer, 'loadJavaScriptModule')) {
		        // Nové API pro ES6 moduly
		        $pageRenderer->loadJavaScriptModule($jsModule);
		    } else {
		        // Fallback pro starší verze
		        $pageRenderer->loadRequireJsModule($jsModule);
		    }
		}
	}
	
	protected function addLangLabels(array $langLabels): void
	{
		$pageRenderer = $this->getPageRenderer();
		
		foreach ($langLabels as $langLabelSource) {
			$fileParts = GeneralUtility::trimExplode(':', $langLabelSource, true, 2);
			
			$file = 'EXT:'. $fileParts[0] .'/Resources/Private/Language/locallang';
			if (count($fileParts) > 1) {
				$file .= '_'. $fileParts[1];
			}
			
			$pageRenderer->addInlineLanguageLabelFile($file .'.xlf');
		}
	}
	
	protected function getTranslateTools(): TranslationConfigurationProvider
	{
		return GeneralUtility::makeInstance(TranslationConfigurationProvider::class);
	}
	
	protected function getPageRenderer(): PageRenderer
	{
		return $this->pageRenderer;
	}
	
	protected function getDocHeader(): DocHeaderComponent
	{
		return $this->moduleTemplate->getDocHeaderComponent();
	}
	
	protected function getUriBuilder(): UriBuilder
	{
		return GeneralUtility::makeInstance(UriBuilder::class);
	}
	
	protected function getDefaultActionName(): string
	{
		return 'index';
	}
	
	protected function redirectToAction(string $action): ResponseInterface
	{
		$route = $this->getModuleName();
		
		if ($action != $this->getDefaultActionName()) {
			$route .= '_'. $action;
		}
		
		return $this->redirectToUri($this->getUriBuilder()->buildUriFromRoute($route));
	}
	
	protected function getLanguageService(): LanguageService
	{
		return $GLOBALS['LANG'];
	}
	
	protected function getUserAuth(): BackendUserAuthentication
	{
		return $GLOBALS['BE_USER'];
	}
}