<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility as ExtbaseUtility;

/**
 * Aktualizovaná třída pro TYPO3 v13
 * 
 * POZOR: Původní metody pro registraci backend modulů jsou označeny jako deprecated.
 * V TYPO3 v13 se doporučuje používat:
 * 1. PHP Attributes na Controller třídách (AsController, AsModule)
 * 2. Configuration/Backend/Modules.php soubor
 * 3. Configuration/Backend/Routes.php pro routing
 */
class ExtensionUtility
{
    /**
     * @deprecated V TYPO3 v13 používejte AsController attribute nebo Configuration/Backend/Modules.php
     */
	public static function registerBackendMainModule(string $extKey, string $moduleKey, string $position = null): void
	{
		// Zkontrolujeme, jestli starý způsob registrace ještě funguje
		if (!class_exists('TYPO3\\CMS\\Extbase\\Utility\\ExtensionUtility')) {
		    throw new \RuntimeException(
		        'ExtbaseUtility\\ExtensionUtility class not found. ' .
		        'Please migrate to modern backend module registration using Configuration/Backend/Modules.php file.'
	        );
		}
		
		$extModuleKey = static::getBackendModuleExtKey($extKey);
		$moduleName = GeneralUtility::underscoredToUpperCamelCase($moduleKey);
		
		try {
			// POZOR: Tato metoda může být deprecated v TYPO3 v13
			ExtbaseUtility\ExtensionUtility::registerModule(
				'Erigo.' . $extModuleKey,
				$moduleKey,
				'',
				'',
				[],
				[
					'access' => 'user,group',
					'icon' => 'EXT:' . $extKey . '/Resources/Public/Icons/BackendModule/' . $moduleName . '.svg',
					'labels' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_mod_' . $moduleKey . '.xlf',
					'navigationComponentId' => '',
					'inheritNavigationComponentFromMainModule' => false,
				]
			);
			
			if ($position !== null) {
				static::moveBackendMainModule($extModuleKey . $moduleName, $position);
			}
		} catch (\Exception $e) {
			// Logujeme varování pro deprecated functionality
			trigger_error(
				'registerBackendMainModule is deprecated in TYPO3 v13. Use Configuration/Backend/Modules.php instead. Error: ' . $e->getMessage(),
				E_USER_DEPRECATED
			);
		}
	}

    /**
     * @deprecated V TYPO3 v13 používejte AsController attribute nebo Configuration/Backend/Modules.php
     */
	public static function registerBackendSubModules(string $extKey, string $mainModuleKey, array $subModules): void
	{
		// Zkontrolujeme, jestli starý způsob registrace ještě funguje
		if (!class_exists('TYPO3\\CMS\\Extbase\\Utility\\ExtensionUtility')) {
		    throw new \RuntimeException(
		        'ExtbaseUtility\\ExtensionUtility class not found. ' .
		        'Please migrate to modern backend module registration using Configuration/Backend/Modules.php file.'
	        );
		}
		
		$extModuleKey = static::getBackendModuleExtKey($extKey);
		$mainModuleName = GeneralUtility::underscoredToUpperCamelCase($mainModuleKey);
		
		foreach ($subModules as $subModuleKey => $subModuleControllerActions) {
			$subModuleName = GeneralUtility::underscoredToUpperCamelCase($subModuleKey);
		
			try {
				// POZOR: Tato metoda může být deprecated v TYPO3 v13
				ExtbaseUtility\ExtensionUtility::registerModule(
					'Erigo.' . $extModuleKey,
					$mainModuleKey,
					$subModuleKey,
					'',
					$subModuleControllerActions,
					[
						'access' => 'user,group',
						'icon' => 'EXT:' . $extKey . '/Resources/Public/Icons/BackendModule/' . $mainModuleName . '/' . 
							$subModuleName . '.svg',
						'labels' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_mod_' .
							$mainModuleKey . '_' . $subModuleKey . '.xlf',
					]
				);
			} catch (\Exception $e) {
				// Logujeme varování pro deprecated functionality
				trigger_error(
					'registerBackendSubModules is deprecated in TYPO3 v13. Use Configuration/Backend/Modules.php instead. Error: ' . $e->getMessage(),
					E_USER_DEPRECATED
				);
			}
		}
	}
	
    /**
     * @deprecated V TYPO3 v13 modul ordering se řeší jinak
     */
	public static function moveBackendMainModule(string $moduleToMove, string $newPosition): void
	{
		// Kontrola existence modulů může být jiná v v13
		if (!isset($GLOBALS['TBE_MODULES']) || !array_key_exists($moduleToMove, $GLOBALS['TBE_MODULES'])) {
			throw new \Exception(
				'The "' . $moduleToMove . '" main module does not exist in $GLOBALS["TBE_MODULES"]. ' .
				'Module registration may have changed in TYPO3 v13.'
			);
		}
		
		$newPositionParts = GeneralUtility::trimExplode(':', $newPosition, true, 2);
		
		if (!in_array($newPositionParts[0], ['top', 'bottom', 'before', 'after'])) {
			throw new \Exception(
				'Possible keywords for $newPosition variable are "top", "bottom", "before:", "after". But "' . $newPosition . '" given.'
			);
		}
		
		if (in_array($newPositionParts[0], ['before', 'after']) && empty($newPositionParts[1])) {
			throw new \Exception(
				'The "' . $newPositionParts[0] . '" keyword must specify target module name.'
			);
		}
		
		// Původní implementace pro přesun modulů
		// POZOR: V TYPO3 v13 se může změnit způsob organizace modulů
		$modulesList = explode(',', $GLOBALS['TBE_MODULES']['_PATHS']['']);
		$moduleIndex = array_search($moduleToMove, $modulesList);
		
		if ($moduleIndex === false) {
			return;
		}
		
		unset($modulesList[$moduleIndex]);
		
		switch ($newPositionParts[0]) {
			case 'top':
				array_unshift($modulesList, $moduleToMove);
				break;
			case 'bottom':
				array_push($modulesList, $moduleToMove);
				break;
			case 'before':
				$targetIndex = array_search($newPositionParts[1], $modulesList);
				if ($targetIndex !== false) {
					array_splice($modulesList, $targetIndex, 0, $moduleToMove);
				}
				break;
			case 'after':
				$targetIndex = array_search($newPositionParts[1], $modulesList);
				if ($targetIndex !== false) {
					array_splice($modulesList, $targetIndex + 1, 0, $moduleToMove);
				}
				break;
		}
		
		$GLOBALS['TBE_MODULES']['_PATHS'][''] = implode(',', $modulesList);
	}
	
	/**
	 * Pomocná metoda pro získání extension key ve správném formátu
	 */
	public static function getBackendModuleExtKey(string $extKey): string
	{
		return GeneralUtility::underscoredToUpperCamelCase($extKey);
	}
	
	/**
	 * Vytvoří moderní konfiguraci modulu pro TYPO3 v13
	 * 
	 * @param string $identifier
	 * @param array $configuration
	 * @return array Konfigurace připravená pro Configuration/Backend/Modules.php
	 */
	public static function createModuleConfiguration(string $identifier, array $configuration): array
	{
		$defaultConfiguration = [
			'parent' => 'web',
			'position' => ['after' => '*'],
			'access' => 'user',
			'iconIdentifier' => 'content-elements-general',
			'labels' => 'LLL:EXT:' . ($configuration['extKey'] ?? 'erigo_base') . '/Resources/Private/Language/locallang_mod.xlf',
			'extensionName' => $configuration['extensionName'] ?? '',
			'controllerActions' => $configuration['controllerActions'] ?? [],
		];
		
		return array_merge($defaultConfiguration, $configuration);
	}
	
	/**
	 * Vytvořit routing konfiguraci pro TYPO3 v13
	 * 
	 * @param string $routeIdentifier
	 * @param string $path
	 * @param string $target
	 * @return array
	 */
	public static function createRouteConfiguration(string $routeIdentifier, string $path, string $target): array
	{
		return [
			'path' => $path,
			'target' => $target,
			'methods' => ['GET', 'POST'],
		];
	}
	
	/**
	 * Helper pro vytvoření backend module configuration
	 * Kompatibilní s TYPO3 v13 způsobem konfigurace
	 */
	public static function generateModulesPhpConfiguration(string $extKey, array $modules): string
	{
		$config = "<?php\n\n";
		$config .= "/**\n";
		$config .= " * Backend module configuration for $extKey\n";
		$config .= " * Compatible with TYPO3 v13\n";
		$config .= " */\n\n";
		$config .= "return [\n";
		
		foreach ($modules as $moduleKey => $moduleConfig) {
			$config .= "    '$moduleKey' => [\n";
			foreach ($moduleConfig as $key => $value) {
				if (is_string($value)) {
					$config .= "        '$key' => '$value',\n";
				} elseif (is_array($value)) {
					$config .= "        '$key' => " . var_export($value, true) . ",\n";
				} else {
					$config .= "        '$key' => " . var_export($value, true) . ",\n";
				}
			}
			$config .= "    ],\n";
		}
		
		$config .= "];\n";
		
		return $config;
	}
}