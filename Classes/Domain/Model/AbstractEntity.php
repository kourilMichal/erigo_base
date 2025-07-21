<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM;
use TYPO3\CMS\Extbase\DomainObject as ExtbaseDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

abstract class AbstractEntity extends ExtbaseDomainObject\AbstractEntity
{
    #[ORM\Transient]
	private ?array $rawData = null;
	
	public function __construct()
	{
	//	parent::__construct();
		
		$this->initializeObject();
	}
	
	public function initializeObject(): void
	{
		/**
		 * Object is empty here.
		 * 
		 * Method is called after classic constructor or after create from database:
		 * @see \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::createEmptyObject()
		 * @see \TYPO3\CMS\Extbase\Object\Container\Container::initializeObject()
		 */
		
		$reflectionService = GeneralUtility::makeInstance(ReflectionService::class);
		$classSchema = $reflectionService->getClassSchema(get_class($this));
		$dataMap = GeneralUtility::makeInstance(DataMapFactory::class)->buildDataMap(get_class($this));
		
		foreach ($this->_getProperties() as $propertyName => $propertyValue) {
			$property = $classSchema->getProperty($propertyName);
			
			if ($property->getType() == ObjectStorage::class && $dataMap->isPersistableProperty($propertyName)) {
				$this->initializeObjectStorage($propertyName);
			}
		}
	}
	
	protected function initializeObjectStorage(string $propertyName): void
	{
		$this->_setProperty($propertyName, new ObjectStorage());
	}
	
	protected function getValueArray(string $string): array
	{
		return GeneralUtility::trimExplode(',', $string, true);
	}
	
	protected function getCsvArray(string $string): array
	{
		return explode(',', $string);
	}
	
	protected function getCsvString(array $array): string
	{
		return implode(',', $array);
	}
	
	protected function getLazyObject(object $object = null): ?ExtbaseDomainObject\AbstractEntity
	{
		if ($object instanceof LazyLoadingProxy) {
			$object = $object->_loadRealInstance();
		}
		
		return $object;
	}
	
	protected function getFirstObjectFromStorage(ObjectStorage $storage): ?ExtbaseDomainObject\AbstractEntity
	{
		$firstEntity = null;
		
		foreach ($storage as $entity) {
			$firstEntity = $entity;
			break;
		}
		
		return $firstEntity;
	}
	
	public function getRawData(): array
	{
		if ($this->rawData === null) {
			$rawData = [];
			
			foreach ($this->_getProperties() as $property => $value) {
				if (is_object($value)) {
					$rawData[$property] = null;
					
					if ($value instanceof LazyLoadingProxy) {
						$value = $this->getLazyObject($value);
					}
					
					if ($value instanceof ExtbaseDomainObject\AbstractEntity) {
						$rawData[$property] = $value->getUid();
					
					} else if ($value instanceof ObjectStorage) {
						$rawData[$property] = $value->count();
					
					} else if ($value instanceof \DateTime) {
						$rawData[$property] = $value->getTimestamp();
					} 
					
				} else {
					$rawData[$property] = $value;
				}
			}
			
			$this->rawData = $rawData;
		}
		
		return $this->rawData;
	}
}