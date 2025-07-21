<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Provider;

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Erigo\ErigoBase\Domain\Repository\AbstractRepository;
use Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface;

class DateFilterProvider extends AbstractOptionsFilterProvider
{
	const TYPE_DATE = 'date';
	const TYPE_YEARS = 'years';
	
	protected ?string $endDateProperty = null;
	
	/**
	 * @see \Erigo\ErigoBase\Filter\Provider\AbstractFilterProvider::getFormElement()
	 */
	public function getFormElement(): string
	{
		if ($this->getItemSettings('type') == self::TYPE_DATE) {
			return FilterProviderInterface::FORM_ELEMENT_DATE;
		}
		
		return parent::getFormElement();
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Provider\AbstractFilterProvider::applyValue()
	 */
	public function applyValue(
	    AbstractRepository $repository, 
	    QueryInterface $query, 
	    mixed $value,
    ): ConstraintInterface 
    {
		$property = $this->item->getQueryProperty();
		
		if ($this->getItemSettings('type') == self::TYPE_YEARS) {
			if ($this->canHaveMultipleValues()) {
				$constraints = [];
				
				foreach ($value as $year) {
					$constraints[] = $this->getRangeConstraint($query, $property, [
							'from' => $year .'-01-01',
							'to' => $year .'-12-31',
						]);
				}
				
				return $query->logicalOr($constraints);
				
			} else {
				$value = [
						'from' => $value .'-01-01',
						'to' => $value .'-12-31',
					];
			}
		}
		
		return $this->getRangeConstraint($query, $property, $value);
	}

	public function getRangeConstraint(QueryInterface $query, string $property, mixed $value): ConstraintInterface
	{
		if (array_key_exists('from', $value) && array_key_exists('to', $value)) {
			if ($this->endDateProperty == null) {
				return $query->between($property, $value['from'], $value['to']);
				
			} else {
				return $query->logicalOr([
						$query->logicalAnd([
								$query->greaterThanOrEqual($property, $value['from']),
								$query->lessThanOrEqual($property, $value['to']),
								$query->lessThanOrEqual($this->endDateProperty, '0000-00-00'),
							]),
						$query->logicalAnd([
								$query->greaterThanOrEqual($property, $value['from']),
								$query->lessThanOrEqual($property, $value['to']),
								$query->greaterThanOrEqual($this->endDateProperty, $value['to']),
							]),
						$query->logicalAnd([
								$query->lessThanOrEqual($property, $value['from']),
								$query->greaterThan($property, '0000-00-00'),
								$query->greaterThanOrEqual($this->endDateProperty, $value['from']),
								$query->lessThanOrEqual($this->endDateProperty, $value['to']),
							]),
						$query->logicalAnd([
								$query->lessThanOrEqual($property, $value['from']),
								$query->greaterThan($property, '0000-00-00'),
								$query->greaterThanOrEqual($this->endDateProperty, $value['to']),
							]),
						$query->logicalAnd([
								$query->greaterThanOrEqual($property, $value['from']),
								$query->lessThanOrEqual($this->endDateProperty, $value['to']),
								$query->greaterThan($this->endDateProperty, '0000-00-00'),
							]),
					]);
			}
			
		} elseif (array_key_exists('from', $value)) {
			if ($this->endDateProperty == null) {
				return $query->greaterThanOrEqual($property, $value['from']);
				
			} else {
				return $query->logicalOr([
						$query->logicalAnd([
								$query->greaterThanOrEqual($property, $value['from']),
								$query->lessThanOrEqual($this->endDateProperty, '0000-00-00'),
							]),
						$query->logicalAnd([
								$query->greaterThanOrEqual($this->endDateProperty, $value['from']),
								$query->greaterThan($property, '0000-00-00'),
							]),
					]);
			}
			
		} elseif (array_key_exists('to', $value)) {
			return $query->logicalAnd([
					$query->lessThanOrEqual($property, $value['to']),
					$query->greaterThan($property, '0000-00-00'),
				]);
		}
		
		throw new \Exception('"From" or "To" value is missing.');
	}
	
	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\OptionsFilterProviderInterface::getAllOptions()
	 */
	public function getAllOptions(): array
	{
		return [];
	}
}