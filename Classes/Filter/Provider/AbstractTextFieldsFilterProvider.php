<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Provider;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Erigo\ErigoBase\Domain\Repository\AbstractRepository;
use Erigo\ErigoBase\Filter\Interfaces\TextFieldsProviderInterface;

abstract class AbstractTextFieldsFilterProvider extends AbstractFilterProvider implements TextFieldsProviderInterface
{
	/**
	 * @see \Erigo\ErigoBase\Filter\Provider\AbstractFilterProvider::applyValue()
	 */
	public function applyValue(
	    AbstractRepository $repository, 
	    QueryInterface $query, 
	    mixed $value,
    ): ConstraintInterface 
    {
		$fieldConstraints = [];
		
		foreach ($this->getAvailableFields() as $fieldName => $fieldLabel) {
			$fieldConstraints[] = $query->like($fieldName, "%". $value ."%");
		}
		
		return $query->logicalOr($fieldConstraints);
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\TextFieldsProviderInterface::getAvailableFields()
	 */
	public function getAvailableFields(): array
	{
		$fields = $this->getAllFields();
		
		switch ($this->getItemSettings('fieldsMode')) {
			case self::FIELDS_MODE_SELECTED:
				$allFields = $fields;
				$fields = [];
				
				foreach ($this->getSelectedFields() as $selectedField) {
					if (array_key_exists($selectedField, $allFields)) {
						$fields[$selectedField] = $allFields[$selectedField];
					}
				}
				
				break;
		}
		
		return $fields;
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\TextFieldsProviderInterface::getPlaceholder()
	 */
	public function getPlaceholder(): string
	{
		return ($this->getItemSettings('placeholder') ?? '');
	}
	
	protected function getTcaLabel(string $table, string $field): string
	{
		if (
		    array_key_exists($table, $GLOBALS['TCA']) && 
		    array_key_exists($field, $GLOBALS['TCA'][$table]['columns']) &&
			array_key_exists('label', $GLOBALS['TCA'][$table]['columns'][$field]) && 
			$GLOBALS['TCA'][$table]['columns'][$field]['label'] != ''
		) {
			return $GLOBALS['TCA'][$table]['columns'][$field]['label'];
		}
		
		return '['. $field .']';
	}
	
	protected function getSelectedFields(): array
	{
		return GeneralUtility::trimExplode(',', ($this->getItemSettings('selectedFields') ?? ''), true);
	}
}