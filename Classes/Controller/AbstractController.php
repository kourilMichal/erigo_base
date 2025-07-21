<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Controller;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Erigo\ErigoBase\Exception\ExceptionHandler;
use Erigo\ErigoBase\Utility\EmailUtility;

abstract class AbstractController extends ActionController
{
	protected function getExtConf(string $path = '', string $extensionName = null): mixed
	{
		if ($extensionName == null) {
			$extensionName = $this->getRealExtensionName();
		}
		
		return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(
				GeneralUtility::camelCaseToLowerCaseUnderscored($extensionName), 
				$path
			);
	}
	
	protected function getRealExtensionName(): string
	{
		$classParts = explode('\\', get_class($this));
			
		if ($classParts[2] == 'Xclass') {
			return $classParts[3];
		}
		
		return $this->request->getControllerExtensionName();
	}
	
	protected function getRequestParam(string $param): mixed
	{
	    return $this->request->getParsedBody()[$param] ?? $this->request->getQueryParams()[$param] ?? null;
	}
	
	protected function sendEmail(array $options, string $templateName = null, array $variables = []): void
	{
		$variables['baseUrl'] = $this->getBaseUrl();
		
		EmailUtility::sendEmail(
				$options, 
				$templateName, 
				EmailUtility::getEmailRootPaths($this->getRootPaths()), 
				$variables
			);
	}
	
	protected function getRootPaths(): array
	{
	    $frameworkSettings = $this->configurationManager->getConfiguration(
	        ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
        );
		
		return $frameworkSettings['view'];
	}
	
	protected function getBaseUrl(): string
	{
		return $this->request->getAttribute('normalizedParams')->getSiteUrl();
	}
	
	protected function debug($variable, $title = null, $maxDepth = 8): void
	{
		DebuggerUtility::var_dump($variable, $title, $maxDepth);
	}
	
	protected function logException(\Throwable $exception): void
	{
		GeneralUtility::makeInstance(ExceptionHandler::class)->logException($exception);
	}
}