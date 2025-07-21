<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter;

use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Erigo\ErigoBase\Domain\Model\ContentFilter;
use Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface;
use Erigo\ErigoBase\Utility\TsConfigUtility;
use Erigo\ErigoBase\Utility\TypoScriptUtility;

class FilterManager implements SingletonInterface
{
	protected ApplicationType $applicationType;
	protected array $filterValues = [];
	
	public function __construct() 
	{
	    // V TYPO3 v13 může být $GLOBALS['TYPO3_REQUEST'] nedostupný v některých kontextech
	    // Přidáme fallback
	    if (isset($GLOBALS['TYPO3_REQUEST'])) {
	        $this->applicationType = ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST']);
	    } else {
	        // Fallback - zkusíme detekovat typ aplikace jinak
	        $this->applicationType = $this->detectApplicationType();
	    }
	}
	
	/**
	 * Fallback detekce typu aplikace pro případy kdy TYPO3_REQUEST není dostupný
	 */
	private function detectApplicationType(): ApplicationType
	{
	    // Zkusíme detekovat backend/frontend podle prostředí
	    if (defined('TYPO3_MODE') && TYPO3_MODE === 'BE') {
	        return ApplicationType::BACKEND;
	    }
	    
	    // Další možné detekce...
	    if (isset($_SERVER['SCRIPT_NAME']) && str_contains($_SERVER['SCRIPT_NAME'], '/typo3/')) {
	        return ApplicationType::BACKEND;
	    }
	    
	    // Default frontend
	    return ApplicationType::FRONTEND;
	}
	
	/**
	 * @throws \Exception
	 */
	public function getProvider(ContentFilter $filterItem, int $pid = 0): FilterProviderInterface
	{
		$ts = [];
		
		if ($this->applicationType->isBackend()) {
			if ($pid == 0) {
				$pid = TsConfigUtility::getRootPid();
			}
			
			$ts = TsConfigUtility::getTsConfig('tx_erigobase.filters.plugin.'. $filterItem->getPlugin(), $pid);
			
		} elseif ($this->applicationType->isFrontend()) {
		    $ts = TypoScriptUtility::getFrontendSettings(
		        'plugin.tx_erigobase.filters.plugin.'. $filterItem->getPlugin(),
	        );
		}
		
		if (array_key_exists($filterItem->getProperty(), $ts)) {
			$ts = $ts[$filterItem->getProperty()];
			
			if (array_key_exists('provider', $ts) && $ts['provider'] != '') {
				$provider = GeneralUtility::makeInstance($ts['provider']);
				
				if ($provider instanceof FilterProviderInterface) {
				    $provider->setItem($filterItem);
				    
					return $provider;
					
				} else {
					throw new \Exception('Filter provider must implement '. FilterProviderInterface::class .'.');
				}
				
			} else {
				throw new \Exception('Filter provider must be defined (provider = Vendor\Extension\ClassName).');
			}
					
		} else {
			throw new \Exception('There is no definition for "'. $filterItem->getProperty() .'" property of "'. 
					$filterItem->getPlugin() .'" plugin.');
		}
	}
	
	public function setFilterValue(string $plugin, string $slug, mixed $value): void
	{
		if (!array_key_exists($plugin, $this->filterValues)) {
			$this->filterValues[$plugin] = [];
		}
		
		$this->filterValues[$plugin][$slug] = $value;
	}
	
	public function getFilterValue(string $plugin, string $slug): mixed
	{
		if (
		    array_key_exists($plugin, $this->filterValues) && 
		    array_key_exists($slug, $this->filterValues[$plugin])
	    ) {
			return $this->filterValues[$plugin][$slug];
		}
		
		return null;
	}
	
	public function getFilterValues(string $plugin): array
	{
		if (array_key_exists($plugin, $this->filterValues)) {
			return $this->filterValues[$plugin];
		}
		
		return [];
	}
}