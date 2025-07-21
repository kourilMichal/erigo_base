<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Repository;

use TYPO3\CMS\Core\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\{QueryInterface, QueryResultInterface, Repository};
use TYPO3\CMS\Extbase\Reflection\ClassSchema\Property;
use Erigo\ErigoBase\Domain\Model\AbstractEntity;
use Erigo\ErigoBase\Domain\Model\Interfaces\ImportInterface;
use Erigo\ErigoBase\Domain\Repository\Interfaces\ExtendedRepositoryInterface;
use Erigo\ErigoBase\Filter\FilterValue;
use Erigo\ErigoBase\Utility\{DomainUtility, QueryUtility};

abstract class AbstractRepository extends Repository implements ExtendedRepositoryInterface
{
	protected string $tableName;
	
	/**
	 * @see \TYPO3\CMS\Extbase\Persistence\Repository::__construct()
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->tableName = DomainUtility::getTableNameFromClassName($this->objectType);
	}
	
	protected function getQueryBuilder(?QueryInterface $query = null): QueryBuilder
	{
		if ($query instanceof QueryInterface) {
			return QueryUtility::getQueryBuilderFromQuery($query);
		}
		
		return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->tableName);
	}
	
	protected function getSQL(QueryInterface $query): string
	{
		return QueryUtility::getSQL($query);
	}
	
	protected function getSQLWithParams(QueryBuilder $queryBuilder): string
	{
		return QueryUtility::getSQLWithParams($queryBuilder);
	}
	
	protected function prepareOrder(?string $order = null): string
	{
	    if ($order == null) {
	        $order = QueryInterface::ORDER_ASCENDING;
	    }
	    
	    return strtoupper($order);
	}
	
	protected function getPropertyOrder(string $property, ?string $order = null): string
	{
	    $order = $this->prepareOrder($order);
	    $sortProperty = DomainUtility::getProperty($this->getObjectType(), $property);
	    
	    if ($sortProperty instanceof Property && $sortProperty->getType() == 'string') {
	        if ($order == QueryInterface::ORDER_DESCENDING) {
	            $order = ExtendedRepositoryInterface::ORDER_COLLATE_DESCENDING;
	            
	        } else {
	            $order = ExtendedRepositoryInterface::ORDER_COLLATE_ASCENDING;
	        }
	    }
	    
	    return $order;
	}
	
	protected function preparePropertyOrderings(array $orderings): array
	{
	    $newOrderings = [];
	    
	    foreach ($orderings as $property => $order) {
	        if (
	            $order === null || 
	            in_array(strtoupper($order), [QueryInterface::ORDER_ASCENDING, QueryInterface::ORDER_DESCENDING])
            ) {
	            $order = $this->getPropertyOrder($property, $order);
	        }
	        
	        $newOrderings[$property] = $order;
	    }
	    
	    return $newOrderings;
	}
	
	/**
	 * @see \Erigo\ErigoBase\Domain\Repository\Interfaces\ExtendedRepositoryInterface::findByUids()
	 */
	public function findByUids(array $uids = []): QueryResultInterface
	{
		$query = $this->createQuery();
		
		if (count($uids) > 0) {
			$l10nParentProperty = DomainUtility::getProperty($this->getObjectType(), 'l10nParent');
			
			if ($l10nParentProperty instanceof Property) {
				$query->matching($query->logicalOr(
						$query->in('uid', $uids),
						$query->in('l10nParent', $uids)
					));
				
			} else {
				$query->matching($query->in('uid', $uids));
			}
		}
		
		return $query->execute();
	}
	
	/**
	 * @see \Erigo\ErigoBase\Domain\Repository\Interfaces\ExtendedRepositoryInterface::findAndSortByUids()
	 */
	public function findAndSortByUids(array $uids = []): array
	{
		return $this->sortByUids($this->findByUids($uids), $uids);
	}
	
	protected function sortByUids(\Iterator|array $unsortedObjects, array $uids): array
	{
		$sortedObjects = [];
		$cache = [];
		
		if (count($uids) > 0) {
			foreach ($unsortedObjects as $object) {
				$cache[$object->getUid()] = $object;
			}
			
			foreach ($uids as $uid) {
				if (array_key_exists($uid, $cache)) {
					$sortedObjects[] = $cache[$uid];
				}
			}
		}
		
		return $sortedObjects;
	}
	
	public function findAllFromPid(
	    int $pid = null, 
	    bool $includeHidden = false, 
	    ?int $sysLangUid = null,
    ): QueryResultInterface 
    {
		$query = $this->createQuery();
		$query->getQuerySettings()->setIgnoreEnableFields($includeHidden);
		
		if ($sysLangUid !== null) {
			$query->getQuerySettings()->setLanguageUid($sysLangUid);
		}
		
		if ($pid !== null) {
			$query->getQuerySettings()->setStoragePageIds([$pid]);
		}
		
		return $query->execute();
	}
	
	public function findByFilter(
	    array $filter,
	    ?array $orderings = null,
	    int $limit = 0,
	    ?int $pid = null, 
		bool $includeHidden = false,
    ): QueryResultInterface
	{
		$query = $this->getFilterQuery($filter, $pid, $includeHidden);

		if (is_array($orderings)) {
			$query->setOrderings($orderings);
		}
		
		if ($limit > 0) {
			$query->setLimit($limit);
		}
		
		return $query->execute();
	}
	
	public function getFilterQuery(array $filter = [], ?int $pid = null, bool $includeHidden = false): QueryInterface
	{
		$query = $this->createQuery();
		$query->getQuerySettings()->setIgnoreEnableFields($includeHidden);
		
		if ($pid !== null) {
			$query->getQuerySettings()->setStoragePageIds([$pid]);
		}
		
		$constraints = $this->applyFilter($query, $filter);
		
		if (count($constraints) > 0) {
			$query->matching($query->logicalAnd(...$constraints));
		}
		
		return $query;
	}
	
	protected function applyFilter(QueryInterface $query, array $filter = []): array
	{
		$constraints = [];
		
		foreach ($filter as $field => $value) {
			if ($value instanceof FilterValue) {
				$filterValue = $value;
				
				$constraints[] = $filterValue->applyValue($this, $query, $field);
				
			} else {
				$constraints[] = $this->applyFilterValue($query, str_replace('_', '.', $field), $value);
			}
		}
		
		return $constraints;
	}
    
    public function applyFilterValue(
        QueryInterface $query,
        string $field,
        mixed $value, 
		int $operator = QueryInterface::OPERATOR_EQUAL_TO,
    ): ConstraintInterface
    {
		$constraint = null;
		
		if ($value instanceof ConstraintInterface) {
			return $value;
		}
		
		if ($operator == QueryInterface::OPERATOR_EQUAL_TO && is_array($value)) {
			$operator = QueryInterface::OPERATOR_IN;
		}
		
		if ($operator == QueryInterface::OPERATOR_NOT_EQUAL_TO && is_array($value)) {
			return $query->logicalNot($query->in($field, $value));
		}
		
		switch ($operator) {
			case QueryInterface::OPERATOR_EQUAL_TO:
				$constraint = $query->equals($field, $value);
				break;
				
			case QueryInterface::OPERATOR_NOT_EQUAL_TO:
				$constraint = $query->logicalNot($query->equals($field, $value));
				break;
				
			case QueryInterface::OPERATOR_LESS_THAN:
				$constraint = $query->lessThan($field, $value);
				break;
				
			case QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO:
				$constraint = $query->lessThanOrEqual($field, $value);
				break;
				
			case QueryInterface::OPERATOR_GREATER_THAN:
				$constraint = $query->greaterThan($field, $value);
				break;
				
			case QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO:
				$constraint = $query->greaterThanOrEqual($field, $value);
				break;
				
			case QueryInterface::OPERATOR_IN:
				$constraint = $query->in($field, $value);
				break;
				
			case QueryInterface::OPERATOR_LIKE:
				$constraint = $query->like($field, "%". $value ."%");
				break;
				
			case QueryInterface::OPERATOR_CONTAINS:
				$constraint = $this->applyFilterMmValue($query, $field, $value);
				break;
				
			case ExtendedRepositoryInterface::OPERATOR_BETWEEN:
				if (
				    !is_array($value) ||
				    (!array_key_exists('from', $value) && !array_key_exists('to', $value))
				) {
					throw new \Exception(
					    'Value must be array with the "from" or "to" keys when using the BETWEEN operator.',
				    );
				}
				
				if (array_key_exists('from', $value) && array_key_exists('to', $value)) {
					$constraint = $query->between($field, $value['from'], $value['to']);
					
				} else {
					if (array_key_exists('from', $value)) {
						$constraint = $query->greaterThanOrEqual($field, $value['from']);
					}
					
					if (array_key_exists('to', $value)) {
						$constraint = $query->lessThanOrEqual($field, $value['to']);
						
					/*
						$constraint = $query->logicalAnd([
							$query->lessThanOrEqual($field, $value['to']),
							$query->logicalNot(
								$query->equals($field, null)
							),
						]);
					*/
					}
				}
				break;
				
			default:
				throw new \Exception('Unsupported operator "'. $operator .'".');
				break;
		}
		
		return $constraint;
    }

	public function applyFilterMmValue(QueryInterface $query, string $field, mixed $value): ConstraintInterface
	{
		if (!is_array($value)) {
			$value = [$value];
		}
		
		if (count($value) > 1) {
			$valueConstraints = [];
			
			foreach ($value as $singleValue) {
				$valueConstraints[] = $query->contains($field, $singleValue);
			}
			
			return $query->logicalOr(...$valueConstraints);
		}
		
		return $query->contains($field, $value);
	}
	
	/**
	 * @see \Erigo\ErigoBase\Domain\Repository\Interfaces\ExtendedRepositoryInterface::getObjectType()
	 */
	public function getObjectType(): string
	{
		return $this->objectType;
	}
	
	/**
	 * @see \Erigo\ErigoBase\Domain\Repository\Interfaces\ExtendedRepositoryInterface::getTableName()
	 */
	public function getTableName(): string
	{
		return $this->tableName;
	}
}