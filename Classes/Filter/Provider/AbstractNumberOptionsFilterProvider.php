<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Provider;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Erigo\ErigoBase\Domain\Repository\AbstractRepository;
use Erigo\ErigoBase\Filter\Interfaces\NumberOptionsFilterProviderInterface;

abstract class AbstractNumberOptionsFilterProvider extends AbstractDbValueOptionsFilterProvider implements NumberOptionsFilterProviderInterface
{
	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\AbstractFilterProvider::applyValue()
	 */
	public function applyValue(
	    AbstractRepository $repository, 
	    QueryInterface $query, 
	    mixed $value,
    ): ConstraintInterface 
    {
		$rangeConstraints = [];

		if (!$this->isRangeElement()) {
			if (!is_array($value)) {
				$value = [$value];
			}
			
			$multipleRangeConstraints = [];
			
			foreach ($value as $singleValue) {
				$rangeNumbers = explode('-', $singleValue, 2);
				$range = [];
				
				if ($rangeNumbers[0] != '') {
					$range['from'] = $rangeNumbers[0];
				}
				
				if (count($rangeNumbers) > 1 && $rangeNumbers[1] != '') {
					$range['to'] = $rangeNumbers[1];
				}
				
				$multipleRangeConstraints[] = $this->getRangeConstraint($repository, $query, $range);
			}
			
			$rangeConstraints[] = $query->logicalOr($multipleRangeConstraints);
			
		} else {
			$rangeConstraints[] = $this->getRangeConstraint($repository, $query, $value);
		}
		
		$baseConstraint = $this->getBaseConstraint($query);
		
		if ($baseConstraint instanceof ConstraintInterface) {
			$rangeConstraints[] = $baseConstraint;
		}
		
		return $query->logicalAnd($rangeConstraints);
	}
	
	protected function getRangeConstraint(
	    AbstractRepository $repository, 
	    QueryInterface $query, 
	    array $range,
    ): ConstraintInterface 
    {
		$rangeConstraints = [];
		
		if (array_key_exists('from', $range)) {
			$rangeConstraints[] = $repository->applyFilterValue(
					$query, 
					$this->item->getQueryProperty(), 
					$range['from'], 
					QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,
				);
		}
		
		if (array_key_exists('to', $range)) {
			$toOperator = QueryInterface::OPERATOR_LESS_THAN;
			if ($this->getItemSettings('differentIntervalBoundaries')) {
				$toOperator = QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO;
			}
			
			$rangeConstraints[] = $repository->applyFilterValue(
					$query, 
					$this->item->getQueryProperty(), 
					$range['to'], 
					$toOperator,
				);
		}
		
		return $query->logicalAnd($rangeConstraints);
	}
	
	protected function getBaseConstraint(QueryInterface $query): ?ConstraintInterface
	{
		return $query->greaterThan($this->item->getQueryProperty(), 0);
	}
	
	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\OptionsFilterProviderInterface::getAllOptions()
	 */
	public function getAllOptions(): array
	{
		$options = [];

		if ($this->getItemSettings('rangeType') == NumberOptionsFilterProviderInterface::RANGE_TYPE_MANUAL) {
			$ranges = GeneralUtility::trimExplode(',', $this->getItemSettings('ranges'), true);
			
			foreach ($ranges as $range) {
				$numbers = GeneralUtility::trimExplode('-', $range, false, 2);
				
				if (count($numbers) < 2) {
					continue;
				}
				
				if ($numbers[0] == '') {
					$options[$numbers[0] .'-'. $numbers[1]] = $this->formatNumberOption($numbers[1], false, 'less');
					
				} elseif ($numbers[1] == '') {
					$options[$numbers[0] .'-'. $numbers[1]] = $this->formatNumberOption($numbers[0], false, 'more');
					
				} else {
					$options[$numbers[0] .'-'. $numbers[1]] = $this->formatNumberOption($numbers[0], true) .' - '.
							$this->formatNumberOption($numbers[1]);
				}
			}
			
		} else {
			$valuesRange = $this->getValuesRange();
			
			$options = $this->getNumberOptions($valuesRange['min'], $valuesRange['max']);
		}
		
		return $options;
	}
	
	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\NumberOptionsFilterProviderInterface::getNumberOptions()
	 */
	public function getNumberOptions(float $minValue, float $maxValue): array
	{
		$options = [];
		
		if ($maxValue >= $minValue && $minValue > 0) {
			$range = abs($maxValue - $minValue);
			
			$minStep = (float) $this->getItemSettings('minStep');
			$maxItems = (int) $this->getItemSettings('maxItems');
			$multiples = [1, 2, 5];

			$power = 0;
				
			if ($range >= 1) {
				$step = 0;
			
				for ($power = 0; $power < 10; $power++) {
					foreach ($multiples as $multiple) {
						$currentStep = $multiple * pow(10, $power);
			
						if ($currentStep < $minStep) {
							continue;
						}
			
						if (ceil($range / $currentStep) <= $maxItems) {
							$minOption = 0;
							
							while ($minOption < $minValue) {
								$minOption += $currentStep;
							}
							
							$minOption -= $currentStep;
							
							if ($minOption + ($currentStep * $maxItems) >= $maxValue) {
								$step = $currentStep;
								break 2;
							}
						}
					}
				}
			
				if ($step > 0) {
					$current = 0;
					if ($this->getItemSettings('minValue') != '') {
						$current = (float) $this->getItemSettings('minValue');
					}
										
					$rangeType = $this->getItemSettings('rangeType');
						
					while ($current < ($maxValue + $step)) {
						if ($current > $minValue) {
							$rangeMin = $current - $step;
							$rangeMax = $current;
							
							if ($this->getItemSettings('differentIntervalBoundaries')) {
								$rangeMax -= pow(10, $this->getNumberOfDecimalDigits($step));
							}
							
							switch ($rangeType) {
								case NumberOptionsFilterProviderInterface::RANGE_TYPE_ABOVE_ONLY:
									$options['-'. $rangeMax] = $this->getNumberOptionText(
									    $rangeMin, 
									    $rangeMax, 
									    $rangeType,
									);
									break;
									
								case NumberOptionsFilterProviderInterface::RANGE_TYPE_BELOW_ONLY:
									$options[$rangeMin .'-'] = $this->getNumberOptionText(
									    $rangeMin, 
									    $rangeMax, 
									    $rangeType,
									);
									break;
								
								default:
									$options[$rangeMin .'-'. $rangeMax] = $this->getNumberOptionText(
									    $rangeMin, 
									    $rangeMax, 
									    $rangeType,
									);
									break;
							}
							
						}
			
						$current += $step;
					}
				}
			}
		}
		
		return $options;
	}
	
	protected function getNumberOptionText(float $rangeMin, float $rangeMax, string $rangeType): string
	{
		$text = '';
		
		switch ($rangeType) {
			case NumberOptionsFilterProviderInterface::RANGE_TYPE_ABOVE_ONLY:
				$text = LocalizationUtility::translate(
						'filter.range.above_only', 
						'erigo_base', 
						[$this->formatNumberOption($rangeMax)],
					);
				break;
				
			case NumberOptionsFilterProviderInterface::RANGE_TYPE_BELOW_ONLY:
				$text = LocalizationUtility::translate(
						'filter.range.below_only', 
						'erigo_base', 
						[$this->formatNumberOption($rangeMin)],
					);
				break;
			
			default:
				$text = $this->formatNumberOption($rangeMin, true) .' - '. $this->formatNumberOption($rangeMax);
				break;
		}
		
		return $text;
	}
	
	protected function getNumberOfDecimalDigits(float $number): int
	{
		$current = $number - floor($number);
		
		for ($decimals = 0; ceil($current); $decimals++) {
			$current = ($number * pow(10, $decimals + 1)) - floor($number * pow(10, $decimals + 1));
		}
		
		return $decimals;
	}
	
	protected function formatNumberOption(float $number, bool $firstInRange = false, string $suffix = null): string
	{
		if ($suffix != null) {
			return LocalizationUtility::translate(
					'filter.range.suffix_'. $suffix, 
					'erigo_base', 
					[$number],
				);
		}
		
		return (string) $number;
	}
}