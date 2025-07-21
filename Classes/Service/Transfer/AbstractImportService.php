<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Service\Transfer;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Reflection;
use TYPO3\CMS\Extbase\Service\CacheService;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Erigo\ErigoBase\Domain\Model\Interfaces\ImportInterface;
use Erigo\ErigoBase\Domain\Model\Interfaces\SynchInterface;
use Erigo\ErigoBase\Domain\Model\AbstractEntity;
use Erigo\ErigoBase\Domain\Model\FileReference;
use Erigo\ErigoBase\Service\AbstractTransferService;
use Erigo\ErigoBase\Service\ResourceService;
use Erigo\ErigoBase\Utility\UrlUtility;

abstract class AbstractImportService extends AbstractTransferService
{
	const DEBUG_OLD_DATA = 4;
	const DEBUG_NEW_DATA = 8;

	protected int $storagePid = 0;
	protected float $decreaseLimit = 90;
	protected int $historyItemsLimit = 0;
	protected PersistenceManagerInterface $persistenceManager;
	protected ResourceService $resourceService;
	protected CacheService $cacheService ;
	protected ReflectionService $reflectionService;
	protected Repository $repository;
	protected array $resourceFolders = [];
	protected array $clearCachePages = [];
	
	public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager)
	{
		$this->persistenceManager = $persistenceManager;
	}
	
	public function injectResourceService(ResourceService $resourceService)
	{
		$this->resourceService = $resourceService;
	}
	
	public function injectCaheService(CacheService $cacheService)
	{
		$this->cacheService = $cacheService;
	}
	
	public function injectReflectionService(Reflection\ReflectionService $reflectionService)
	{
		$this->reflectionService = $reflectionService;
	}
	

	abstract protected function getModelClassName(): string;
	
	
	public function importData(array $data, string $source, array $languages = null): bool
	{
		$itemClassName = $this->getModelClassName();
		if (!is_a($itemClassName, ImportInterface::class, true)) {
			throw new \DomainException(
			    'Item class ('. $itemClassName .') must implement '. ImportInterface::class .'.',
		    );
		}

		$this->removeTCASettings($this->getClassesToRemoveFromTCA(), true, true);
			
		$itemsCount = count($data);
		$hideOldItems = $this->shouldHideOldItems($itemClassName);

		if ($itemsCount < 1) {
			throw new \InvalidArgumentException('No items passed. ['. static::class .']');
		}
		
	//	$oldItems = $this->repository->findAllImportedV2($source, $this->storagePid, $hideOldItems, $languages);
		$oldItems = $this->repository->findAllImported(
		    $source, 
		    $this->storagePid, 
		    $hideOldItems, 
		    $this->hasLanguages($itemClassName),
	    );
		
		$oldItemsCount = count($oldItems);
		
		if ($this->debugLevel & static::DEBUG_OLD_DATA) {
			DebuggerUtility::var_dump($oldItems, 'Old data', 10);
		}
		
		if ($this->debugLevel & static::DEBUG_NEW_DATA) {
			DebuggerUtility::var_dump($data, 'New data', 10);
		}
		
		if ($hideOldItems) {
			$oldItemsCount = 0;
			
			foreach ($oldItems as $oldLanguageItems) {
				foreach ($oldLanguageItems as $languageUid => $oldItem) {
					if ($languageUid > 0) {
						continue;
					}
					
					if (!$oldItem->getHidden()) {
						$oldItemsCount++;
					}
				}
			}
		}

		$this->validateNewRecordsCount($oldItemsCount, $itemsCount);

		foreach ($data as $importId => $itemLanguages) {
			ksort($itemLanguages);
			
			$parentItem = null;
			
			foreach ($itemLanguages as $languageUid => $itemData) {
				if ($languageUid > 0 && !($parentItem instanceof AbstractEntity)) {
					continue;
				}
				
				$insert = true;
				$item = GeneralUtility::makeInstance($itemClassName);
				
				if (
				    array_key_exists($importId, $oldItems) && 
				    array_key_exists($languageUid, $oldItems[$importId])
			    ) {
					$insert = false;
					$item = $oldItems[$importId][$languageUid];
					
					unset($oldItems[$importId][$languageUid]);
				}
				
				$this->initializeObject($item, $languageUid, $parentItem, (string) $importId, $source);
				$this->processItem($item, $itemData, $parentItem);

				if ($this->debugLevel & static::DEBUG_SINGLE_ITEM) {
					DebuggerUtility::var_dump($item, 'Item data', 10);
				}
				
				if ($insert) {
					$this->repository->add($item);
					
				} else {
					$this->repository->update($item);
				}

				if ($languageUid == 0) {
					$parentItem = $item;
				}
			}
		}
		
	// hide or delete old items
		foreach ($oldItems as $oldLanguageItems) {
			foreach ($oldLanguageItems as $languageUid => $oldItem) {
				$this->addObjectSynchHistoryItem($oldItem, SynchInterface::ACTION_DELETE, $source);
				
				if ($hideOldItems) {
					if (!$oldItem->getHidden()) {
						$oldItem->setHidden(true);
						$this->repository->update($oldItem);
					}
					
				} else {
					$this->repository->remove($oldItem);
				}
			}
		}
		
	// persist & clear cache
		if ($this->debugLevel == 0) {
			$this->persistenceManager->persistAll();
			$this->clearCache();
		}
		
		return true;
	}

	/**
	 * @see \Erigo\ErigoBase\Service\AbstractTransferService::processItem()
	 */
	protected function processItem(AbstractEntity $item, array $itemData, AbstractEntity $parentItem = null): void
	{
		$classSchema = $this->getClassSchema();
		
	// hidden
		if ($classSchema->hasProperty('hidden') && array_key_exists('hidden', $itemData)) {
			$item->setHidden($itemData['hidden']);
		}
		
	// title & slug
		if ($classSchema->hasProperty('title') && array_key_exists('title', $itemData)) {
			$item->setTitle($itemData['title']);
		}

		if ($classSchema->hasProperty('slug')) {
			if (array_key_exists('slug', $itemData)) {
				$item->setSlug($itemData['slug']);
					
			} elseif (!$item->getUid() && $classSchema->hasProperty('title')) {
				/**
				 * @todo unique slug
				 */
					
				$item->setSlug(UrlUtility::sanitizeString(
						$item->getTitle(),
						$this->getTCA($this->getModelClassName())['columns']['slug']['config']
				));
			}
		}

	// texts
		if ($classSchema->hasProperty('perex') && array_key_exists('perex', $itemData)) {
			$item->setPerex($itemData['perex']);
		}

		if ($classSchema->hasProperty('text') && array_key_exists('text', $itemData)) {
			$item->setText($itemData['text']);
		}

	// SEO
		if ($classSchema->hasProperty('metaTitle') && array_key_exists('meta_title', $itemData)) {
			$item->setMetaTitle($itemData['meta_title']);
		}

		if ($classSchema->hasProperty('metaKeywords') && array_key_exists('meta_keywords', $itemData)) {
			$item->setMetaKeywords($itemData['meta_keywords']);
		}

		if ($classSchema->hasProperty('metaDescription') && array_key_exists('meta_description', $itemData)) {
			$item->setMetaDescription($itemData['meta_description']);
		}
		
	// images
		if ($classSchema->hasProperty('images') && array_key_exists('images', $itemData)) {
			$this->processItemImages($item, $itemData['images'], $parentItem);
		}
		
		
	// categories
		if ($classSchema->hasProperty('categories') && array_key_exists('categories', $itemData)) {
			$this->processItemCategories($item, $itemData['categories']);
		}
	}
	
	protected function initializeObject(
	    AbstractEntity $object, 
	    int $languageUid = 0, 
	    AbstractEntity $parentObject = null, 
		string $importId = null, 
	    string $importSource = null,
    ): void 
    {
		$object->setPid($this->storagePid);
		
		if ($object instanceof ImportInterface && !empty($importId) && !empty($importSource)) {
			$object->setImportId($importId);
			$object->setImportSource($importSource);
			
			$synchAction = SynchInterface::ACTION_CREATE;
			
			if ($object->getUid() > 0) {
				$synchAction = SynchInterface::ACTION_UPDATE;
			}
			
			if ($this->shouldHideOldItems($object)) {
				if ($object->getHidden()) {
					$synchAction = SynchInterface::ACTION_RESTORE;
				}
				
				$object->setHidden(false);
			}
			
			$this->addObjectSynchHistoryItem($object, $synchAction);
		}
			
		if ($this->hasLanguages($object)) {
			$object->setSysLanguageUid($languageUid);
		
			if ($languageUid > 0 && $parentObject instanceof AbstractEntity) {
				$object->setL10nParent($parentObject);
				
			} else {
				$object->setL10nParent(null);
			}
		}
	}
	
	protected function addObjectSynchHistoryItem(ImportInterface $object, string $action, string $source = null): void
	{
		if ($source == null) {
			$source = $object->getImportSource();
		}
			
		$object->addSynchHistoryItem([
				'type' => SynchInterface::TYPE_IMPORT,
				'source' => $source,
				'action' => $action,
				
			], $this->historyItemsLimit);
	}
	
	protected function processItemCategories(AbstractEntity $item, array $categories): void
	{
		$categories = array_unique($categories);
		$categories = array_flip($categories);
		
		$allCategories = $this->getAllCategories();
		$toDelete = [];

	// current categories
		foreach ($item->getCategories() as $category) {
			if (array_key_exists($category->getUid(), $categories)) {
				unset($categories[$category->getUid()]);
				
			} else {
				$toDelete[] = $category;
			}
		}
		
	// old categories
		foreach ($toDelete as $category) {
			$item->removeCategory($category);
		}
		
	// new categories
		foreach (array_keys($categories) as $categoryUid) {
			if (!array_key_exists($categoryUid, $allCategories)) {
				continue;
			}
			
			$item->addCategory($allCategories[$categoryUid]);
		}
	}
	
	protected function processItemImages(AbstractEntity $item, array $images, AbstractEntity $parentItem = null): void
	{
		$images = array_unique($images);
		
		$languageUid = 0;
		if ($this->hasLanguages($item)) {
			$languageUid = $item->getSysLanguageUid();
		}
		
	//	$newImages = new ObjectStorage();
		$toInsert = [];
		$toDelete = [];
		
	// parent images
		$parentImages = [];
		if ($parentItem instanceof AbstractEntity) {
			foreach ($parentItem->getImages() as $image) {
				$parentImages[] = $image;
			}
		}

	// current images
		$imageCounter = 0;
		$sortingCounter = 1;
		
		foreach ($item->getImages() as $image) {
			$removeImage = true;
			
			if (array_key_exists($imageCounter, $images)) {
				if (!empty($images[$imageCounter])) {
					$parentImage = null;
					if ($languageUid > 0 && array_key_exists($sortingCounter - 1, $parentImages)) {
						$parentImage = $parentImages[$sortingCounter - 1];
					}
							
					$newImage = $this->getFileReference(
					    $item, 
					    'images', 
					    $images[$imageCounter], 
					    $parentImage, 
					    $sortingCounter,
				    );
					
					if ($newImage instanceof FileReference) {
						if (
						    $newImage->getOriginalResource()->getIdentifier() == 
						        $image->getOriginalResource()->getIdentifier()
					    ) {
							$removeImage = false;
							
						} else {
							$toInsert[] = $newImage;
						}
					//	$newImages->attach($newImage);
						$sortingCounter++;
					}
				}
					
				unset($images[$imageCounter]);
			}
			
			if ($removeImage) {
				$toDelete[] = $image;
			}
			
			$imageCounter++;
		}
		
	// old images
		foreach ($toDelete as $image) {
			$item->removeImage($image);
			
		//	$this->resourceService->removeFileReference($image);
		}

	// new images
		foreach ($toInsert as $image) {
			$item->addImage($image);
		}
		
		foreach ($images as $imageUrl) {
			if (empty($imageUrl)) {
				continue;
			}
			
			$parentImage = null;
			if ($languageUid > 0 && array_key_exists($sortingCounter - 1, $parentImages)) {
				$parentImage = $parentImages[$sortingCounter - 1];
			}
			
			$newImage = $this->getFileReference($item, 'images', $imageUrl, $parentImage, $sortingCounter);
			
			if ($newImage instanceof FileReference) {
				$item->addImage($newImage);
			//	$newImages->attach($newImage);
				$sortingCounter++;
			}
		}
		
	//	$item->setImages($newImages);
	}
	
	protected function getFileReference(
	    AbstractEntity $item, 
	    string $fieldName, 
	    string $fileUrl, 
		FileReference $parentFileReference = null, 
	    int $sorting = 1,
    ): ?FileReference 
    {
		$fileReference = $this->resourceService->createFileReference(
		    $fileUrl, 
		    $this->getResourceFolder($item, $fieldName),
	    );
		
		if ($fileReference instanceof FileReference) {
			$languageUid = 0;
			if ($this->hasLanguages($item)) {
				$languageUid = $item->getSysLanguageUid();
			}
		
			$this->initializeObject($fileReference, $languageUid, $parentFileReference);
			
			$fileReference->setForeignTable($this->getTableName(get_class($item)));
			$fileReference->setForeignObject($item);
			$fileReference->setFieldName($fieldName);
			$fileReference->setSortingForeign($sorting);
		}
		
		return $fileReference;
	}
	
	protected function getResourceFolder(AbstractEntity $item, string $fieldName): string
	{
		$resourceFolder = null;
		$tableName = $this->getTableName(get_class($item));
		
		if (
		    array_key_exists($tableName, $this->resourceFolders) && 
		    array_key_exists($fieldName, $this->resourceFolders[$tableName])
	    ) {
			$resourceFolder = $this->resourceFolders[$tableName][$fieldName];
			
			if ($item instanceof ImportInterface && $fieldName == 'images') {
				$resourceFolder .= '/'. $item->getImportId();
			}
		
			return $resourceFolder;
		}
		
		throw new \LogicException(
		    'No resource folder for field "'. $fieldName .'" of table "'. $tableName .'" is defined.',
	    );
	}
	
	public function addResourceFolder(string $className, string $fieldName, string $folderIdentifier): void
	{
		$destinationFolder = $this->resourceService->getDestinationFolder($folderIdentifier);
		
		if ($destinationFolder instanceof Resource\Folder) {
			$tableName = $this->getTableName($className);
			
			if (!array_key_exists($tableName, $this->resourceFolders)) {
				$this->resourceFolders[$tableName] = [];
			}
			
			$this->resourceFolders[$tableName][$fieldName] = trim($destinationFolder->getIdentifier(), '/');
			
		} else {
			throw new \LogicException(
			    'Resource folder "'. $folderIdentifier .'" does not exist and cannot be created.',
		    );
		}
	}
	
	protected function shouldHideOldItems(object|string $objectOrClassName): bool
	{
		if (is_object($objectOrClassName)) {
			$objectOrClassName = get_class($objectOrClassName);
		}
		
		$ctrl = $this->getTCA($objectOrClassName)['ctrl'];
		
		return (array_key_exists('enablecolumns', $ctrl) && array_key_exists('disabled', $ctrl['enablecolumns']));
	}
	
	protected function hasLanguages($objectOrClassName): bool
	{
		if (is_object($objectOrClassName)) {
			$objectOrClassName = get_class($objectOrClassName);
		}
		
		$ctrl = $this->getTCA($objectOrClassName)['ctrl'];
		
		return array_key_exists('languageField', $ctrl);
	}
	
	public function setStoragePid(int $storagePid): void
	{
		$this->storagePid = $storagePid;
	}
	
	public function setDecreaseLimit(float $decreaseLimit): void
	{
		$this->decreaseLimit = $decreaseLimit;
	}
	
	public function setHistoryItemsLimit(int $historyItemsLimit): void
	{
		$this->historyItemsLimit = $historyItemsLimit;
	}

	public function validateNewRecordsCount(int $oldCount, int $newCount, string $modelClassName = null): void
	{
		if ($newCount < $oldCount) {
			$decreasePercent = ((1 - ($newCount / $oldCount)) * 100);
	
			if ($this->decreaseLimit > 0 && $decreasePercent > $this->decreaseLimit) {
				if (empty($modelClassName)) {
					$modelClassName = $this->getModelClassName();
				}
				
				throw new \OutOfBoundsException(
					'Reducing the number of records ('. $oldCount .' -> '. $newCount .' ... -'. 
			            round($decreasePercent, 2) .'%) of type '. $modelClassName .
			            ' is higher than the allowed limit. ['. static::class .']'
				);
			}
		}
	}
	
	public function addClearCachePagesByPluginContent(string $pluginName): void
	{
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
		$statement = $queryBuilder
			->select('pid')
			->from('tt_content')
			->where(
					$queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter($pluginName))
				)
			->groupBy('pid')
			->execute();
		
		while ($row = $statement->fetch()) {
			$this->addClearCachePage($row['pid']);
		}
	}
	
	public function addClearCachePage(int $pageUid): void
	{
		$this->clearCachePages[] = $pageUid;
	}
	
	protected function clearCache(): void
	{
		if (count($this->clearCachePages) > 0) {
			$this->cacheService->clearPageCache($this->clearCachePages);
		}
	}
	
	protected function getClassSchema(string $className = null): Reflection\ClassSchema
	{
		if ($className == null) {
			$className = $this->getModelClassName();
		}
		
		return $this->reflectionService->getClassSchema($className);
	}

}