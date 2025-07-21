<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\DataProcessing;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\{Request, RequestInterface, ExtbaseRequestParameters};
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Service\ExtensionService;

abstract class AbstractBreadcrumbProcessor implements BreadcrumbProcessorInterface
{
	public function __construct(
	    protected string $extensionName,
	    protected ExtensionService $extensionService,
	    protected UriBuilder $uriBuilder,
    ) {
		$this->extensionName = GeneralUtility::underscoredToUpperCamelCase($this->extensionName);
		$this->uriBuilder->setRequest($this->getExtbaseRequest());
	}

	/**
	 * @see \Erigo\ErigoBase\DataProcessing\BreadcrumbProcessorInterface::getExtensionName()
	 */
	public function getExtensionName(): string
	{
		return $this->extensionName;
	}
	
	protected function getUrlArguments(string $pluginName): ?array
	{
		return $GLOBALS['TYPO3_REQUEST']->getQueryParams()[$this->getPluginNamespace($pluginName)] ?? null;
	}
	
	protected function getPluginNamespace(string $pluginName): string
	{
		return $this->extensionService->getPluginNamespace($this->getExtensionName(), $pluginName);
	}
	
	protected function getLastIndex(array $breadcrumbs): int
	{
		return count($breadcrumbs) - 1;
	}
	
	protected function getLink(string $pluginName, array $controllerArguments = [], int $pid = 0): ?string
	{
		$urlArguments = $this->getUrlArguments($pluginName);
		
		if (is_array($urlArguments)) {
			$this->uriBuilder->reset();
			
			if ($pid > 0) {
				$this->uriBuilder->setTargetPageUid($pid);
			}
			
			return $this->uriBuilder->uriFor(
					$urlArguments['action'], 
					$controllerArguments, 
					$urlArguments['controller'], 
					$this->getExtensionName(), 
					$pluginName,
				);
		}
		
		return null;
	}
	
	protected function getExtbaseRequest(): RequestInterface
	{
	    $request = $GLOBALS['TYPO3_REQUEST'];
	    
	    return new Request(
	        $request->withAttribute('extbase', new ExtbaseRequestParameters()),
        );
	}
}