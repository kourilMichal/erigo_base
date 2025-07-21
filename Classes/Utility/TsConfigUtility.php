<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class TsConfigUtility
{
	public static function getTsConfig(string $path, int $pid = 0): array
	{
	    // Pokud není PID zadáno, pokusíme se získat root PID
	    if ($pid === 0) {
	        $pid = static::getRootPid();
	    }
	    
		$tsConfig = GeneralUtility::removeDotsFromTS(BackendUtility::getPagesTSconfig($pid));
		
		if ($path != null) {
			// V TYPO3 v13 používáme modernější způsob pro dotted path access
			try {
			    return ArrayUtility::getValueByPath($tsConfig, $path, '.');
			} catch (\Exception $e) {
			    // Fallback na starší způsob
			    return static::getValueByPathLegacy($tsConfig, $path);
			}
		}
		
		return $tsConfig;
	}
	
	/**
	 * Získání root PID pro backend kontext
	 * V TYPO3 v13 aktualizováno pro lepší site detection
	 */
	public static function getRootPid(): int
	{
	    // Metoda 1: Pokud máme dostupný request s backend user
	    if (isset($GLOBALS['BE_USER']) && method_exists($GLOBALS['BE_USER'], 'getSessionData')) {
	        $userData = $GLOBALS['BE_USER']->getSessionData('erigo_base_root_pid');
	        if ($userData && is_numeric($userData)) {
	            return (int) $userData;
	        }
	    }
	    
	    // Metoda 2: Pokusíme se získat z aktuální stránky v backend kontextu
	    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	        $pageId = (int) $_GET['id'];
	        try {
	            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);
	            return $site->getRootPageId();
	        } catch (SiteNotFoundException $e) {
	            // Page není součástí žádné site, použijeme ji jako root
	            return $pageId;
	        }
	    }
	    
	    // Metoda 3: Získáme první dostupnou site
	    try {
	        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
	        $sites = $siteFinder->getAllSites();
	        
	        if (count($sites) > 0) {
	            $firstSite = reset($sites);
	            return $firstSite->getRootPageId();
	        }
	    } catch (\Exception $e) {
	        // Site finder selhal
	    }
	    
	    // Metoda 4: Fallback na první stránku v databázi
	    try {
	        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
	            ->getQueryBuilderForTable('pages');
	        
	        $result = $queryBuilder
	            ->select('uid')
	            ->from('pages')
	            ->where(
	                $queryBuilder->expr()->eq('is_siteroot', 1),
	                $queryBuilder->expr()->eq('deleted', 0)
	            )
	            ->orderBy('uid', 'ASC')
	            ->setMaxResults(1)
	            ->executeQuery();
	            
	        $row = $result->fetchAssociative();
	        if ($row) {
	            return (int) $row['uid'];
	        }
	    } catch (\Exception $e) {
	        // Database query failed
	    }
	    
	    // Poslední fallback
	    return 1;
	}
	
	/**
	 * Uložení root PID pro aktuální backend session
	 */
	public static function setRootPid(int $pid): void
	{
	    if (isset($GLOBALS['BE_USER']) && method_exists($GLOBALS['BE_USER'], 'setSessionData')) {
	        $GLOBALS['BE_USER']->setSessionData('erigo_base_root_pid', $pid);
	    }
	}
	
	/**
	 * Legacy fallback pro path access - pro kompatibilitu se starším kódem
	 */
	protected static function getValueByPathLegacy(array $tsConfig, string $path): array
	{
	    $path = str_replace('\.', '[DOT]', $path);
		$pathParts = explode('.', $path);
		
		foreach ($pathParts as $pathPart) {
			$pathPart = str_replace('[DOT]', '.', $pathPart);
			
			if (array_key_exists($pathPart, $tsConfig)) {
				$tsConfig = $tsConfig[$pathPart];
				
			} else {
				return [];
			}
		}
		
		return is_array($tsConfig) ? $tsConfig : [];
	}
	
	/**
	 * Získání TSconfig pro specifickou site
	 */
	public static function getTsConfigForSite(string $path, int $siteRootPid): array
	{
	    return static::getTsConfig($path, $siteRootPid);
	}
	
	/**
	 * Získání TSconfig s fallback na parent stránky
	 */
	public static function getTsConfigWithFallback(string $path, int $pid): array
	{
	    $tsConfig = static::getTsConfig($path, $pid);
	    
	    // Pokud není nalezeno a nejsme na root, zkusíme parent
	    if (empty($tsConfig) && $pid > 1) {
	        $parentPid = static::getParentPageId($pid);
	        if ($parentPid > 0) {
	            return static::getTsConfigWithFallback($path, $parentPid);
	        }
	    }
	    
	    return $tsConfig;
	}
	
	/**
	 * Pomocná metoda pro získání parent page ID
	 */
	protected static function getParentPageId(int $pid): int
	{
	    try {
	        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
	            ->getQueryBuilderForTable('pages');
	        
	        $result = $queryBuilder
	            ->select('pid')
	            ->from('pages')
	            ->where(
	                $queryBuilder->expr()->eq('uid', $pid),
	                $queryBuilder->expr()->eq('deleted', 0)
	            )
	            ->executeQuery();
	            
	        $row = $result->fetchAssociative();
	        return $row ? (int) $row['pid'] : 0;
	        
	    } catch (\Exception $e) {
	        return 0;
	    }
	}
}