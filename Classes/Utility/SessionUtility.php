<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class SessionUtility
{
	public static function hasData(string $namespace, string $key): bool
	{
		$data = static::getData($namespace);
		
		if (is_array($data)) {
			return array_key_exists($key, $data);
		}
		
		return false;
	}
	
	public static function getData(string $namespace, string $key = null): mixed
	{
		$feUserAuth = static::getFeUserAuth();
		
		if ($feUserAuth != null) {
			$sessionData = $feUserAuth->getKey(static::getSessionType(), $namespace);
			
			if (!is_array($sessionData)) {
				$sessionData = [];
			}
			
			if ($key === null) {
				return $sessionData;
			}
			
			if (array_key_exists($key, $sessionData)) {
				return $sessionData[$key];
			}
		}
		
		return null;
	}
	
	public static function setData(string $namespace, string $key, mixed $value): void
	{
		$feUserAuth = static::getFeUserAuth();
		
		if ($feUserAuth != null) {
			$sessionData = static::getData($namespace);
			$sessionData[$key] = $value;
			
			$feUserAuth->setKey(static::getSessionType(), $namespace, $sessionData);
			$feUserAuth->storeSessionData();
		}
	}
	
	public static function unsetData(string $namespace, string $key): void
	{
		$feUserAuth = static::getFeUserAuth();
		
		if ($feUserAuth != null) {
			$sessionData = static::getData($namespace);
	
			if (array_key_exists($key, $sessionData)) {
				unset($sessionData[$key]);
				
				$feUserAuth->setKey(static::getSessionType(), $namespace, $sessionData);
				$feUserAuth->storeSessionData();
			}
		}
	}
	
	/**
	 * Získání FrontendUserAuthentication - aktualizováno pro TYPO3 v13
	 * V TYPO3 v13 je $GLOBALS['TSFE']->fe_user deprecated
	 */
	protected static function getFeUserAuth(): ?FrontendUserAuthentication
	{
		// Metoda 1: Zkusíme získat přes request (nejmodernější způsob)
		if (isset($GLOBALS['TYPO3_REQUEST'])) {
			$request = $GLOBALS['TYPO3_REQUEST'];
			$frontendUser = $request->getAttribute('frontend.user');
			
			if ($frontendUser instanceof FrontendUserAuthentication) {
				return $frontendUser;
			}
		}
		
		// Metoda 2: Fallback přes Context (funguje i v v13)
		try {
			$context = GeneralUtility::makeInstance(Context::class);
			$userAspect = $context->getAspect('frontend.user');
			
			// Pokud máme userAspect, zkusíme získat skutečný FrontendUserAuthentication objekt
			if ($userAspect && method_exists($userAspect, 'get')) {
				// V některých verzích je možné získat user objekt přímo
				$user = $userAspect->get('user');
				if ($user instanceof FrontendUserAuthentication) {
					return $user;
				}
			}
		} catch (\Exception $e) {
			// Context není dostupný nebo nemáme frontend.user aspect
		}
		
		// Metoda 3: Legacy fallback pro starší kód - DEPRECATED v v13
		// Toto bude postupně odstraněno, ale zatím může fungovat
		if (isset($GLOBALS['TSFE']) && 
		    property_exists($GLOBALS['TSFE'], 'fe_user') && 
		    $GLOBALS['TSFE']->fe_user instanceof FrontendUserAuthentication) {
			return $GLOBALS['TSFE']->fe_user;
		}
		
		return null;
	}
	
	protected static function getSessionType(): string
	{
		try {
			$context = GeneralUtility::makeInstance(Context::class);
			
			if ($context->getPropertyFromAspect('frontend.user', 'isLoggedIn')) {
				return 'user';
			}
		} catch (\Exception $e) {
			// Fallback pokud context není dostupný
		}
		
		return 'ses';
	}
	
	/**
	 * Pomocná metoda pro získání User ID z contextu
	 */
	public static function getCurrentUserId(): int
	{
		try {
			$context = GeneralUtility::makeInstance(Context::class);
			return (int) $context->getPropertyFromAspect('frontend.user', 'id');
		} catch (\Exception $e) {
			return 0;
		}
	}
	
	/**
	 * Pomocná metoda pro kontrolu, zda je uživatel přihlášen
	 */
	public static function isUserLoggedIn(): bool
	{
		try {
			$context = GeneralUtility::makeInstance(Context::class);
			return (bool) $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
		} catch (\Exception $e) {
			return false;
		}
	}
	
	/**
	 * Pomocná metoda pro získání frontend uživatelských skupin
	 */
	public static function getUserGroups(): array
	{
		try {
			$context = GeneralUtility::makeInstance(Context::class);
			return $context->getPropertyFromAspect('frontend.user', 'groupIds') ?? [];
		} catch (\Exception $e) {
			return [];
		}
	}
}