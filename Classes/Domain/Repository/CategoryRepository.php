<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2023 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class CategoryRepository extends AbstractRepository
{
	protected $defaultOrderings = [
		'sorting' => QueryInterface::ORDER_ASCENDING,
	];
}