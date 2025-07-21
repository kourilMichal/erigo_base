<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2022 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Xclass\Extbase\Persistence\Storage;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\{JoinInterface, SelectorInterface, SourceInterface};
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Erigo\ErigoBase\Domain\Repository\Interfaces\ExtendedRepositoryInterface;

class QueryParser extends Typo3DbQueryParser
{
    protected ?string $languageCollation = null;
    
    /**
     * @see \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::parseOrderings()
     */
    protected function parseOrderings(array $orderings, SourceInterface $source)
    {
        $collateOrderings = [
            ExtendedRepositoryInterface::ORDER_COLLATE_ASCENDING,
            ExtendedRepositoryInterface::ORDER_COLLATE_DESCENDING,
        ];
        
        $directOrderings = [
            ExtendedRepositoryInterface::ORDER_DIRECT_ASCENDING,
            ExtendedRepositoryInterface::ORDER_DIRECT_DESCENDING,
        ];
        
        foreach ($orderings as $propertyName => $order) {
            if (in_array($order, $collateOrderings)) {
                $collation = $this->getLanguageCollation();
                $sqlOrder = $this->convertCollateOrderToSqlOrder($order);
                
                if (!empty($collation)) {
                    $this->addPropertyOrder($source, $propertyName, "COLLATE '". $collation ."' ". $sqlOrder);
                    
                } else {
                    $this->addPropertyOrder($source, $propertyName, $sqlOrder);
                }
                
            } else if (in_array($order, $directOrderings)) {
                $this->queryBuilder->addOrderBy($propertyName, $this->convertDirectOrderToSqlOrder($order));
                
            } else {
                $this->addPropertyOrder($source, $propertyName, $order);
            }
        }
    }
    
    /**
     * @see \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::parseOrderings()
     */
    protected function addPropertyOrder(SourceInterface $source, string $propertyName, string $order): void
    {
        $className = null;
        $tableName = '';
        
        if ($source instanceof SelectorInterface) {
            $className = $source->getNodeTypeName();
            $tableName = $this->dataMapper->convertClassNameToTableName($className);
            $fullPropertyPath = '';
            
            while (strpos($propertyName, '.') !== false) {
                $this->addUnionStatement($className, $tableName, $propertyName, $fullPropertyPath);
            }
            
        } elseif ($source instanceof JoinInterface) {
            $tableName = $source->getLeft()->getSelectorName();
        }
        
        $columnName = $this->dataMapper->convertPropertyNameToColumnName($propertyName, $className);
        
        if ($tableName !== '') {
            $this->queryBuilder->addOrderBy($tableName . '.' . $columnName, $order);
            
        } else {
            $this->queryBuilder->addOrderBy($columnName, $order);
        }
    }
    
    protected function convertCollateOrderToSqlOrder(string $collateOrder): string
    {
        $sqlOrder = QueryInterface::ORDER_ASCENDING;
        
        if ($collateOrder == ExtendedRepositoryInterface::ORDER_COLLATE_DESCENDING) {
            $sqlOrder = QueryInterface::ORDER_DESCENDING;
        }
        
        return $sqlOrder;
    }
    
    protected function convertDirectOrderToSqlOrder(string $directOrder): string
    {
        $sqlOrder = QueryInterface::ORDER_ASCENDING;
        
        if ($directOrder == ExtendedRepositoryInterface::ORDER_DIRECT_DESCENDING) {
            $sqlOrder = QueryInterface::ORDER_DESCENDING;
        }
        
        return $sqlOrder;
    }
    
    protected function getLanguageCollation(): ?string
    {
        if ($this->languageCollation === null) {
            $siteLanguage = $this->getCurrentSiteLanguage();
                    
            if ($siteLanguage instanceof SiteLanguage) {
                $languageConfig = $siteLanguage->toArray();
                
                if (array_key_exists('collation', $languageConfig) && !empty($languageConfig['collation'])) {
                    $this->languageCollation = $languageConfig['collation'];
                }
            }
        }
        
        return $this->languageCollation;
    }
    
    protected function getCurrentSiteLanguage(): ?SiteLanguage
    {
        $siteLanguage = null;
        $serverRequest = $GLOBALS['TYPO3_REQUEST'];
        
        if ($serverRequest instanceof ServerRequestInterface) {
            $applicationType = ApplicationType::fromRequest($serverRequest);
        
            if ($applicationType->isFrontend()) {
                $siteLanguage = $serverRequest->getAttribute('language');
                
            } else if ($applicationType->isBackend()) {
                $siteLanguage = $this->getBackendSiteLanguage($serverRequest);
            }
        }
        
        return $siteLanguage;
    }
	
	protected function getBackendSiteLanguage(ServerRequestInterface $serverRequest): ?SiteLanguage
	{
	    $siteLanguage = null;
        $moduleData = $GLOBALS['BE_USER']->getModuleData($this->getBackendModuleName($serverRequest));
        
    // get data from backend module
        if (is_array($moduleData)) {
            if (
                array_key_exists('list', $moduleData) &&
                is_array($moduleData['list']) &&
                array_key_exists('pid', $moduleData['list'])
            ) {
                $langId = 0;
                if (array_key_exists('lang', $moduleData['list'])) {
                    $langId = $moduleData['list']['lang'];
                }
                
                try {
                    $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
                    $siteLanguage = $siteFinder
                        ->getSiteByPageId($moduleData['list']['pid'])
                        ->getLanguageById($langId);
                } 
                catch (SiteNotFoundException $e) {}
                catch (\InvalidArgumentException $e) {}
            }
        }
	    
	    return $siteLanguage;
	}
	
	protected function getBackendModuleName(ServerRequestInterface $serverRequest): string
	{
		$moduleName = $serverRequest->getAttribute('route')->getPath();
		$moduleName = str_replace('/', '_', trim($moduleName, '/'));
		
		$moduleNameParts = explode('_', $moduleName, 3);
		
		if (count($moduleNameParts) > 2) {
    		return $moduleNameParts[1] .'_'. $moduleNameParts[2];
		}
		
		return implode('_', $moduleNameParts);
	}
}