<?php

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class TypoScriptUtility implements SingletonInterface
{
    const THEME_DEFAULT = 'default';
    
	protected static array $tsBySite = [];
	
	public static function getSiteSettings(Site\Entity\Site $site, string $key = null): array
	{
	    $siteId = $site->getIdentifier();
	    
	    if (!array_key_exists($siteId, static::$tsBySite)) {
	        static::$tsBySite[$siteId] = [];
	    }
	    
	    if (!array_key_exists('settings', static::$tsBySite[$siteId])) {
	        static::$tsBySite[$siteId]['settings'] = static::loadSiteSettings($site);
	    }
	    
	    if ($key != null) {
	        return ArrayUtility::getValueByPath(static::$tsBySite[$siteId]['settings'], $key, '.');
	    }
	    
	    return static::$tsBySite[$siteId]['settings'];
	}
	
	public static function getSiteConstants(Site\Entity\Site $site, string $key = null): array
	{
	    $siteId = $site->getIdentifier();
	    
	    if (!array_key_exists($siteId, static::$tsBySite)) {
	        static::$tsBySite[$siteId] = [];
	    }
	    
	    if (!array_key_exists('constants', static::$tsBySite[$siteId])) {
	        static::$tsBySite[$siteId]['constants'] = static::loadSiteConstants($site);
	    }
	    
	    if ($key != null) {
	        return ArrayUtility::getValueByPath(static::$tsBySite[$siteId]['constants'], $key, '.');
	    }
	    
	    return static::$tsBySite[$siteId]['constants'];
	}
	
	public static function getSiteConstantsByPid(int $pid, string $key = null): array
	{
	    try {
	        $site = GeneralUtility::makeInstance(Site\SiteFinder::class)->getSiteByPageId($pid);
	        
	        return static::getSiteConstants($site, $key);
	        
	    } catch (SiteNotFoundException $e) {}
	    
	    return [];
	}
	
	public static function getSiteConstantValue(Site\Entity\Site $site, string $key): mixed
	{
	    try {
	        return ArrayUtility::getValueByPath(static::getSiteConstants($site), $key, '.');
	    } catch (\Exception $e) {
	        return null;
	    }
	}
	
	public static function getSiteConstantValueByPid(int $pid, string $key): mixed
	{
	    try {
	        return ArrayUtility::getValueByPath(static::getSiteConstantsByPid($pid), $key, '.');
	    } catch (\Exception $e) {
	        return null;
	    }
	}
	
	public static function getSiteTheme(Site\Entity\Site $site): string
	{
	    return static::getSiteConstantValue($site, 'page.theme.name') ?? self::THEME_DEFAULT;
	}
	
	public static function getFrontendTheme(): string
	{
	    return static::getSiteTheme(static::getFrontendSite());
	}
	
	public static function getFrontendSettings(string $key = null): array
	{
	    return static::getSiteSettings(static::getFrontendSite(), $key);
	}
	
	public static function getFrontendSettingsValue(string $key): mixed
	{
	    try {
	        return ArrayUtility::getValueByPath(static::getFrontendSettings(), $key, '.');
	    } catch (\Exception $e) {
	        return null;
	    }
	}
	
	public static function getFrontendConstants(string $key = null): array
	{
	    return static::getSiteConstants(static::getFrontendSite(), $key);
	}
	
	public static function getFrontendConstantValue(string $key): mixed
	{
	    try {
	        return ArrayUtility::getValueByPath(static::getSiteConstants(static::getFrontendSite()), $key, '.');
	    } catch (\Exception $e) {
	        return null;
	    }
	}
	
	protected static function getFrontendSite(): Site\Entity\Site
	{
	    if (!isset($GLOBALS['TYPO3_REQUEST'])) {
	        throw new \Exception('TYPO3_REQUEST is not available.');
	    }
	    
	    $applicationType = ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST']);
	    
	    if (!$applicationType->isFrontend()) {
	        throw new \Exception('Only allowed on frontend.');
	    }
	    
	    $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
	    
	    if (!$site instanceof Site\Entity\Site) {
	        throw new \Exception('Site not found in request.');
	    }
	    
	    return $site;
	}
	
	protected static function loadSiteConstants(Site\Entity\Site $site): array
	{
		$flatConstants = [];
		$siteConstants = [];
		$templateService = static::getSiteTemplateService($site);
		
		if ($templateService instanceof TemplateService) {
			if (!is_array($templateService->flatSetup) || count($templateService->flatSetup) === 0) {
				$templateService->generateConfig();
			}
			
			$flatConstants = $templateService->flatSetup;
		}
		
		foreach ($flatConstants as $constant => $value) {
			$constantParts = explode('.', $constant);
			$lastPart = count($constantParts) - 1;
			$constantArray = [];
			$currentArray = &$constantArray;
			
			foreach ($constantParts as $index => $constantPart) {
				if ($index == $lastPart) {
					$currentArray[$constantPart] = $value;
					
				} else {
					$currentArray[$constantPart] = [];
					$currentArray = &$currentArray[$constantPart];
				}
			}
			
			$siteConstants = array_merge_recursive($siteConstants, $constantArray);
		}
		
		if (count($siteConstants) > 0) {
		    try {
		        $scssConstants = ArrayUtility::getValueByPath(
		            $siteConstants, 
		            'plugin.bootstrap_package.settings.scss', 
		            '.'
		        );
		        
		        if (is_array($scssConstants)) {
			        foreach ($scssConstants as $key => $value) {
				        $i = 0;
					        
				        while (substr($value, 0, 1) == '$' && $i < 10) {
					        if (array_key_exists(substr($value, 1), $scssConstants)) {
						        $value = $scssConstants[substr($value, 1)];
						        
					        } else {
						        break;
					        }
					        
					        $i++;
				        }
				        
				        $scssConstants[$key] = $value;
			        }
			        
			        ArrayUtility::setValueByPath($siteConstants, 'plugin.bootstrap_package.settings.scss', $scssConstants, '.');
		        }
		    } catch (\Exception $e) {
		        // SCSS constants processing failed, continue without them
		    }
		}
		
		return $siteConstants;
	}
	
	protected static function loadSiteSettings(Site\Entity\Site $site): array
	{
	    $siteTypoScript = [];
	    $templateService = static::getSiteTemplateService($site);
		
		if ($templateService instanceof TemplateService) {
		    $siteTypoScript = static::convertTypoScriptToArray($templateService->setup);
		}
		
		return $siteTypoScript;
	}
	
	/**
	 * Získání TemplateService - aktualizováno pro TYPO3 v13
	 * V TYPO3 v13 je $GLOBALS['TSFE'] deprecated
	 */
	protected static function getSiteTemplateService(Site\Entity\Site $site): ?TemplateService
	{
	    if (!isset($GLOBALS['TYPO3_REQUEST'])) {
	        return null;
	    }
	    
	    $applicationType = ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST']);
	    
	    if ($applicationType->isBackend()) {
	        $templateService = GeneralUtility::makeInstance(TemplateService::class);
	        $templateService->runThroughTemplates(BackendUtility::BEgetRootLine($site->getRootPageId()));
	        
	        return $templateService;
			
	    } elseif ($applicationType->isFrontend()) {
	        // V TYPO3 v13 získáváme TypoScript přes request attributes
	        $typoscriptSetup = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript');
	        
	        if ($typoscriptSetup !== null) {
	            // Vytvoříme mock TemplateService pro kompatibilitu
	            $templateService = GeneralUtility::makeInstance(TemplateService::class);
	            $templateService->setup = $typoscriptSetup->getSetupArray();
	            $templateService->flatSetup = $typoscriptSetup->getFlatSetup();
	            
	            return $templateService;
	        }
	        
	        // Fallback pro starší verze nebo edge cases
	        if (isset($GLOBALS['TSFE']) && 
	            property_exists($GLOBALS['TSFE'], 'tmpl') && 
	            $GLOBALS['TSFE']->tmpl instanceof TemplateService) {
	            return $GLOBALS['TSFE']->tmpl;
	        }
	    }
	    
	    return null;
	}
	
	public static function convertTypoScriptToArray($typoScript): array
	{
		return GeneralUtility::makeInstance(TypoScriptService::class)->convertTypoScriptArrayToPlainArray($typoScript);
	}
	
	public static function convertArrayToTypoScript(array $array): array
	{
		return GeneralUtility::makeInstance(TypoScriptService::class)->convertPlainArrayToTypoScriptArray($array);
	}
	
	/**
	 * Pomocná metoda pro získání TypoScript setup z requestu (TYPO3 v13)
	 */
	public static function getTypoScriptSetupFromRequest(): array
	{
	    if (!isset($GLOBALS['TYPO3_REQUEST'])) {
	        return [];
	    }
	    
	    $typoscriptSetup = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript');
	    
	    if ($typoscriptSetup !== null && method_exists($typoscriptSetup, 'getSetupArray')) {
	        return $typoscriptSetup->getSetupArray();
	    }
	    
	    return [];
	}
	
	/**
	 * Pomocná metoda pro získání flattened TypoScript setup z requestu (TYPO3 v13)
	 */
	public static function getFlatTypoScriptSetupFromRequest(): array
	{
	    if (!isset($GLOBALS['TYPO3_REQUEST'])) {
	        return [];
	    }
	    
	    $typoscriptSetup = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript');
	    
	    if ($typoscriptSetup !== null && method_exists($typoscriptSetup, 'getFlatSetup')) {
	        return $typoscriptSetup->getFlatSetup();
	    }
	    
	    return [];
	}
}