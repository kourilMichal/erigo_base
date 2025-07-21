<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class FileReferenceRepository extends AbstractRepository
{
	protected $defaultOrderings = [
		'sortingForeign' => QueryInterface::ORDER_ASCENDING,
	];
}