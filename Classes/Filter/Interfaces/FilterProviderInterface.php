<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Interfaces;

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Erigo\ErigoBase\Domain\Model\ContentFilter;
use Erigo\ErigoBase\Domain\Repository\AbstractRepository;

interface FilterProviderInterface
{
	const FORM_ELEMENT_CHECKBOXES = 'checkboxes';
	const FORM_ELEMENT_DATE = 'date';
	const FORM_ELEMENT_MULTISELECT = 'multiselect';
	const FORM_ELEMENT_SELECT = 'select';
	const FORM_ELEMENT_NUMBER = 'number';
	const FORM_ELEMENT_TEXT = 'text';
	
	
	public function getFormElement(): string;

	public function prepareValue(mixed $value): mixed;
	
	public function applyValue(
	    AbstractRepository 
	    $repository, 
	    QueryInterface $query, 
	    mixed $value,
    ): ConstraintInterface;

	public function getItem(): ContentFilter;

	public function getItemSettings(?string $key = null);
	
	public function addConstraint(ConstraintInterface|array $constraint): void;
	
	public function getConstraints(): array;
	
	public function getUnit(): ?string;
}