<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\CacheService;

class CacheUtility
{
	public static function clearPageCacheByPluginContent(
	    string $extensionName, 
	    string $pluginName, 
	    string $controller = null, 
		string $action = null,
    ): void 
    {
		$extensionName = str_replace(' ', '', ucwords(str_replace('_', ' ', $extensionName)));
		$pluginSignature = strtolower($extensionName) . '_' . strtolower($pluginName);
		$pages = [];
		
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
		$queryBuilder
			->select('pid')
			->from('tt_content')
			->where(
					$queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter($pluginSignature))
				)
			->groupBy('pid');
		
		if ($controller != null) {
			$scaCondition = $controller .'-&gt;';
			if ($action != null) {
				$scaCondition .= $action;
			}
			
			$queryBuilder->andWhere($queryBuilder->expr()->like(
		        'pi_flexform', 
		        $queryBuilder->createNamedParameter('%'. $scaCondition .'%'),
		    ));
		}
		
		$statement = $queryBuilder->execute();
		
		while ($row = $statement->fetch()) {
			$pages[] = $row['pid'];
		}

		if (count($pages) > 0) {
			$cacheService = GeneralUtility::makeInstance(CacheService::class);
			$cacheService->clearPageCache($pages);
		}
	}
}