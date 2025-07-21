<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\TCA;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Erigo\ErigoBase\Domain\Model\TtContentFilter;
use Erigo\ErigoBase\Domain\Repository\TtContentFilterRepository;
use Erigo\ErigoBase\Filter\FilterManager;
use Erigo\ErigoBase\Filter\Interfaces\{OptionsFilterProviderInterface, TextFieldsProviderInterface};
use Erigo\ErigoBase\Utility\TsConfigUtility;

class FilterHelper implements SingletonInterface
{
	public function getPropertyOptions(array &$params): void
	{
		$pluginKey = $params['row']['plugin'];
		
		if ($pluginKey != '') {
			$properties = TsConfigUtility::getTsConfig(
			    'tx_erigobase.filters.plugin.'. $pluginKey, 
			    $params['row']['pid'],
		    );
			
			foreach ($properties as $property => $propertyConfig) {
				$params['items'][] = [$propertyConfig['label'], $property];
			}
		}
	}
	
	public function getSettingsOptions(): array
	{
		return [
			'default' => 'FILE:EXT:erigo_base/Configuration/FlexForms/_Empty.xml'
		];
	}
	
	public function getOptionsForSelect(array &$params): void
	{
		$pid = 0;
		if (array_key_exists('flexParentDatabaseRow', $params)) {
			$pid = $params['flexParentDatabaseRow']['pid'];
		}
		
		$filterRepository = GeneralUtility::makeInstance(TtContentFilterRepository::class);
		$filterItem = $filterRepository->findByUid($params['row']['uid']);
		
		if ($filterItem instanceof TtContentFilter) {
			$filterManager = GeneralUtility::makeInstance(FilterManager::class);
			$provider = $filterManager->getProvider($filterItem, $pid);
			
			if ($provider instanceof OptionsFilterProviderInterface) {
				foreach ($provider->getAllOptions() as $value => $label) {
					$params['items'][] = [$label, $value];
				}
				
			} else if ($provider instanceof TextFieldsProviderInterface) {
				foreach ($provider->getAllFields() as $fieldName => $fieldLabel) {
					$params['items'][] = [$fieldLabel, $fieldName];
				}
			}
		}
	}
}