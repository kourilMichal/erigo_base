<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use Erigo\ErigoBase\Domain\Model\AbstractEntity;
use Erigo\ErigoBase\Domain\Repository\CategoryRepository;
use Erigo\ErigoBase\Utility\DomainUtility;

abstract class AbstractTransferService implements SingletonInterface
{
	const DEBUG_SINGLE_ITEM = 1;
	
	protected array $tca = [];
	protected CategoryRepository $categoryRepository;
	protected int $debugLevel = 0;
	protected ?array $allCategories = null;
	
	public function __construct()
	{
		$this->tca = $GLOBALS['TCA'];
	}
	
	public function injectCategoryRepository(CategoryRepository $categoryRepository)
	{
	    $this->categoryRepository = $categoryRepository;
	}
		
	
	abstract protected function processItem(
	    AbstractEntity $item,
	    array $itemData, 
	    AbstractEntity $parentItem = null,
    ): void;
	
	
	protected function getTableName(string $className): string
	{
		return DomainUtility::getTableNameFromClassName($className);
	}
	
	protected function getTCA(string $className): array
	{
		return $this->tca[$this->getTableName($className)];
	}
	
	protected function removeTCASettings(
	    array $classNames, 
	    bool $languageField = false, 
	    bool $enableColumns = false,
    ): void 
    {
		foreach ($classNames as $className) {
			$tableName = $this->getTableName($className);
			
			if ($languageField) {
				unset($GLOBALS['TCA'][$tableName]['ctrl']['languageField']);
			}
	
			if ($enableColumns) {
				unset($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']);
			}
		}
	}
	
	public function getAllCategories(): array
	{
		if ($this->allCategories === null) {
			$this->allCategories = [];
			
			$querySettings = GeneralUtility::makeInstance(QuerySettingsInterface::class);
			$querySettings->setRespectStoragePage(false);
			$categoryRepository->setDefaultQuerySettings($querySettings);
			
			foreach ($this->categoryRepository->findAll() as $category) {
				$this->allCategories[$category->getUid()] = $category;
			}
			
			$defaultQuerySettings = GeneralUtility::makeInstance(QuerySettingsInterface::class);
			$this->categoryRepository->setDefaultQuerySettings($defaultQuerySettings);
		}
		
		return $this->allCategories;
	}

	
	public function setDebugLevel(int $debugLevel): void
	{
		$this->debugLevel = $debugLevel;
	}
}