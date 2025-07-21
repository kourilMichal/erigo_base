<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter;

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Erigo\ErigoBase\Domain\Repository\AbstractRepository;
use Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface;

class FilterValue
{
	public function __construct(
	    protected FilterProviderInterface $provider = null, 
	    protected mixed $value = null, 
	    protected int $operator = QueryInterface::OPERATOR_EQUAL_TO,
    ) {}
	
	public function getProvider(): ?FilterProviderInterface
	{
		return $this->provider;
	}
	
	public function getValue(): mixed
	{
		return $this->value;
	}
	
	public function getOperator(): int
	{
		return $this->operator;
	}

	public function applyValue(
	    AbstractRepository $repository, 
	    QueryInterface $query, 
	    string $field,
    ): ConstraintInterface 
    {
		if ($this->provider instanceof FilterProviderInterface) {
			return $this->provider->applyValue($repository, $query, $this->value);
		}
		
		return $repository->applyFilterValue($query, $field, $this->value, $this->operator);
	}
}