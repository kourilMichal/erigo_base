<?php

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use Erigo\ErigoBase\Domain\Repository\AbstractRepository;

class QueryUtility
{
	/**
	 * @internal only for debugging...
	 * @example \Erigo\ErigoBase\Utility\QueryUtility::getSQL($query)
	 */
	public static function getSQL(QueryInterface $query): string
	{	
		return static::getSQLWithParams(static::getQueryBuilderFromQuery($query));
	}

	/**
	 * @internal only for debugging...
	 * @example \Erigo\ErigoBase\Utility\QueryUtility::getSQLWithParams($queryBuilder)
	 */
	public static function getSQLWithParams(QueryBuilder $queryBuilder): string
	{
		$sql = $queryBuilder->getSQL();
		$params = $queryBuilder->getParameters();
		
		krsort($params);
		
		foreach ($params as $key => $value) {
			switch (gettype($value)) {
				case 'array':
					$values = [];
					
					foreach ($value as $singleValue) {
						switch (gettype($singleValue)) {
							case 'integer':
							case 'boolean':
								$values[] = (int) $singleValue;
								break;
								
							default:
								$values[] = "'". $singleValue ."'";
								break;
						}
					}
					
					$sql = str_replace(':'. $key, implode(", ", $values), $sql);
					break;
					
				case 'integer':
				case 'boolean':
					$sql = str_replace(':'. $key, (int) $value, $sql);
					break;
					
				case 'NULL':
					$sql = str_replace(':'. $key, "NULL", $sql);
					
				default:
					$sql = str_replace(':'. $key, "'". $value ."'", $sql);
					break;
			}
		}
		
		return $sql;
	}
	
	public static function getQueryBuilderFromQuery(QueryInterface $query): QueryBuilder
	{
		/**
		 * @see \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbBackend::getObjectDataByQuery()
		 */
		
		$queryParser = GeneralUtility::makeInstance(Typo3DbQueryParser::class);
		$queryBuilder = $queryParser->convertQueryToDoctrineQueryBuilder($query);
		
		$selectParts = $queryBuilder->getQueryPart('select');
		if ($queryParser->isDistinctQuerySuggested() && !empty($selectParts)) {
			$selectParts[0] = 'DISTINCT ' . $selectParts[0];
			$queryBuilder->selectLiteral(...$selectParts);
		}
		
		if ($query->getOffset()) {
			$queryBuilder->setFirstResult($query->getOffset());
		}
		
		if ($query->getLimit()) {
			$queryBuilder->setMaxResults($query->getLimit());
		}
		
		return $queryBuilder;
	}
	
	public static function getTableNameFromQuery(QueryInterface $query): string
	{
		$source = $query->getSource();
		
		if ($source instanceof Qom\SelectorInterface) {
			return DomainUtility::getTableNameFromClassName($source->getNodeTypeName());
			
		} else if ($source instanceof Qom\JoinInterface) {
			return $source->getLeft()->getSelectorName();
		}
		
		throw new \Exception('Unsupported query source type.');
	}
}