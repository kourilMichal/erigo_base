<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Provider;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Erigo\ErigoBase\Filter\Interfaces\OptionsFilterProviderInterface;

abstract class AbstractOptionsFilterProvider extends AbstractFilterProvider implements OptionsFilterProviderInterface
{
    protected ?array $allOptions = null;
    protected ?array $allPersistenceOptions = null;
    
	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\OptionsFilterProviderInterface::getAvailableOptions()
	 */
	public function getAvailableOptions(): array
	{
		$options = $this->getAllOptions();
		
		switch ($this->getItemSettings('optionsMode')) {
			case self::OPTIONS_MODE_SELECTED:
				$allOptions = $options;
				$options = [];
				
				foreach ($this->getSelectedOptions() as $selectedOption) {
					if (array_key_exists($selectedOption, $allOptions)) {
						$options[$selectedOption] = $allOptions[$selectedOption];
					}
				}
				
				break;
		}
		
		return $options;
	}
	
	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\OptionsFilterProviderInterface::isValidOption()
	 */
	public function isValidOption(mixed $value): bool
	{
	    if ($this->isPersistenceOption($value)) {
	        return true;
	    }
	    
	    return array_key_exists($value, $this->getAvailableOptions());
	}
	
	protected function isPersistenceOption(mixed $value): bool
	{
	    if (
	        MathUtility::canBeInterpretedAsInteger($value) && 
	        is_array($this->allPersistenceOptions) &&
	        array_key_exists($value, $this->allPersistenceOptions)
        ) {
            return true;
        }
        
        return false;
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Provider\AbstractFilterProvider::addConstraint()
	 */
	public function addConstraint(ConstraintInterface|array $constraint): void
	{
		if (is_array($constraint)) {
			foreach ($constraint as $singleConstraint) {
			    if ($this->isPersistenceOption($singleConstraint)) {
		            $this->constraints[] = $this->allPersistenceOptions[$singleConstraint];
			        
			    } else {
				    $this->constraints[] = $singleConstraint;
			    }
			}
			
		} else {
		    if ($this->isPersistenceOption($constraint)) {
	            $this->constraints[] = $this->allPersistenceOptions[$constraint];
		        
		    } else {
			    $this->constraints[] = $constraint;
		    }
		}
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\OptionsFilterProviderInterface::getDefaultOptionLabel()
	 */
	public function getDefaultOptionLabel(): string
	{
		return ($this->getItemSettings('defaultOptionLabel') ?? '');
	}
	
	protected function getTcaItems(string $table, string $column): array
	{
		$options = [];
		
		if (
		    array_key_exists($table, $GLOBALS['TCA']) && 
		    array_key_exists($column, $GLOBALS['TCA'][$table]['columns'])
	    ) {
			$columnConfig = $GLOBALS['TCA'][$table]['columns'][$column]['config'];
			
			if (array_key_exists('items', $columnConfig)) {
				foreach ($columnConfig['items'] as $item) {
					$label = $item[0];
					
					if (substr($label, 0, 4) == 'LLL:') {
						$label = LocalizationUtility::translate($label);
					}
					
					$options[$item[1]] = $label;
				}
			}
		}
		
		return $options;
	}
	
	protected function getSelectedOptions(): array
	{
		return GeneralUtility::trimExplode(',', ($this->getItemSettings('selectedOptions') ?? ''), true);
	}
}