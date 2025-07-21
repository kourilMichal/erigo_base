<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Provider;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Erigo\ErigoBase\Domain\Model\ContentFilter;
use Erigo\ErigoBase\Domain\Repository\AbstractRepository;
use Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface;

abstract class AbstractFilterProvider implements FilterProviderInterface
{
	protected ContentFilter $item;
	protected array $constraints = [];
	
	public function setItem(ContentFilter $item): void
	{
	    $this->item = $item;
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface::getFormElement()
	 */
	public function getFormElement(): string
	{
		return ($this->getItemSettings('formElement') ?? FilterProviderInterface::FORM_ELEMENT_TEXT);
	}
	
	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface::prepareValue()
	 */
	public function prepareValue(mixed $value): mixed
	{
		if ($this->isRangeElement()) {
			return $value;
		}
		
		if ($this->canHaveMultipleValues()) {
			return explode(',', $value);
		}
		
		return (string) $value;
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface::applyValue()
	 */
	public function applyValue(
	    AbstractRepository $repository, 
	    QueryInterface $query, 
	    mixed $value
    ): ConstraintInterface 
    {
		return $repository->applyFilterValue($query, $this->item->getQueryProperty(), $value);
	}
	
	protected function isRangeElement(): bool
	{
		return in_array($this->getFormElement(), [
				FilterProviderInterface::FORM_ELEMENT_DATE,
				FilterProviderInterface::FORM_ELEMENT_NUMBER,
			]);
	}
	
	protected function canHaveMultipleValues(): bool
	{
		return in_array($this->getFormElement(), [
				FilterProviderInterface::FORM_ELEMENT_MULTISELECT, 
				FilterProviderInterface::FORM_ELEMENT_CHECKBOXES,
			]);
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface::getItem()
	 */
	public function getItem(): ContentFilter
	{
		return $this->item;
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface::getItemSettings()
	 */
	public function getItemSettings(?string $key = null): mixed
	{
		$settings = $this->item->getSettingsArray();
		
		if ($key != null) {
			if (array_key_exists($key, $settings)) {
				return $settings[$key];
			}
			
			return null;
		}
		
		return $settings;
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface::addConstraint()
	 */
	public function addConstraint(ConstraintInterface|array $constraint): void
	{
		if (is_array($constraint)) {
			foreach ($constraint as $singleConstraint) {
				$this->constraints[] = $singleConstraint;
			}
			
		} else {
			$this->constraints[] = $constraint;
		}
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface::getConstraints()
	 */
	public function getConstraints(): array
	{
		return $this->constraints;
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface::getUnit()
	 */
	public function getUnit(): ?string
	{
		return null;
	}
}