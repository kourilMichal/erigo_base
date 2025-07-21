<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class ContentFilterRepository extends AbstractRepository
{
	protected $defaultOrderings = [
		'sorting' => QueryInterface::ORDER_ASCENDING,
	];
	
	public function findByContentObjectData(array $contentObjectData): QueryResultInterface
	{
		$query = $this->createQuery();
		$query->getQuerySettings()->setStoragePageIds([$contentObjectData['pid']]);
		
		$contentId = $contentObjectData['uid'];
		if (array_key_exists('_LOCALIZED_UID', $contentObjectData)) {
			$contentId = $contentObjectData['_LOCALIZED_UID'];
		}
		
		$query->matching($query->equals('content_uid', $contentId));
		
		return $query->execute();
	}
}