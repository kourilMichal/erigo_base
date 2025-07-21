<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2022 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Repository\Interfaces;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

interface ExtendedRepositoryInterface
{
	const OPERATOR_BETWEEN = 33;
	
	const ORDER_COLLATE_ASCENDING = 'COLLATE_ASC';
	const ORDER_COLLATE_DESCENDING = 'COLLATE_DESC';
	
	const ORDER_DIRECT_ASCENDING = 'DIRECT_ASC';
	const ORDER_DIRECT_DESCENDING = 'DIRECT_DESC';
	
	public function findByUids(array $uids = []): QueryResultInterface;
	
	public function findAndSortByUids(array $uids = []): array;
	
	public function getObjectType(): string;
	
	public function getTableName(): string;
}