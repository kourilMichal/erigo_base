<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Reflection\ClassSchema\Exception\NoSuchPropertyException;
use TYPO3\CMS\Extbase\Reflection\ClassSchema\Property;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use Erigo\ErigoBase\Domain\Model\Interfaces\EncodeDecodeInterface;

class DomainUtility
{
	public static function getTableNameFromClassName(string $className): string
	{
		$dataMapper = GeneralUtility::makeInstance(Mapper\DataMapper::class);
		
		return $dataMapper->getDataMap($className)->getTableName();
	}
	
	public static function getColumnFromProperty(string $className, string $property): ?string
	{
		$dataMapper = GeneralUtility::makeInstance(Mapper\DataMapper::class);
		$columnMap = $dataMapper->getDataMap($className)->getColumnMap($property);
		
		if ($columnMap instanceof Mapper\ColumnMap) {
			return $columnMap->getColumnName();
		}
		
		return null;
	}
	
	public static function getProperty(string $className, string $propertyName): ?Property
	{
		$propertyParts = explode('.', $propertyName, 2);
		
		$reflectionService = GeneralUtility::makeInstance(ReflectionService::class);
		$classSchema = $reflectionService->getClassSchema($className);
		$property = null;
		
		try {
    		$property = $classSchema->getProperty($propertyParts[0]);
    		
    		if (count($propertyParts) > 1 && $property instanceof Property) {
    			$childClassName = $property->getType();
    			
    			if ($childClassName == ObjectStorage::class) {
    				$childClassName = $property->getElementType();
    			}
    			
    			return static::getProperty($childClassName, $propertyParts[1]);
    		}
		    
		} catch (NoSuchPropertyException $e) {}
		
		return $property;
	}
	
	public static function copyProperties(object $sourceObject, object $targetObject, array $properties): void
	{
		foreach ($properties as $sourceProperty => $targetProperty) {
			if (empty($sourceProperty) || empty($targetProperty)) {
				continue;
			}
			
			$sourceObjectValue = $sourceObject->{'get'. ucfirst($sourceProperty)}();
			
			if ($sourceObjectValue instanceof AbstractEntity && !$sourceObjectValue->getUid()) {
				$cloneObjectValue = clone $sourceObjectValue;
				$sourceObjectValue = $cloneObjectValue;
			}
			
			$targetObject->{'set'. ucfirst($targetProperty)}($sourceObjectValue);
		}
	}


	public static function encode(object $object, array $properties = null): string
	{
		return json_encode(static::getEncodedData($object, $properties));
	}

	public static function getEncodedData(object $object, array $properties = null): array
	{
		$data = [];
		
		if ($properties === null && $object instanceof EncodeDecodeInterface) {
			$properties = $object->getEncodeProperties();
		}
		
		foreach ($properties as $property) {
			$propertyValue = null;
			
			if ($object instanceof EncodeDecodeInterface) {
				$propertyValue = $object->encodeProperty($property);
				
			} else {
				$propertyValue = static::encodeProperty($object, $property);
			}
			
			$data[$property] = $propertyValue;
		}
		
		return $data;
	}
	
	public static function encodeProperty(object $object, string $property): mixed
	{
		$value = $object->{'get'. ucfirst($property)}();
			
		if ($value instanceof AbstractEntity) {
			$value = static::encodePropertyObject($value);
		
		} elseif ($value instanceof ObjectStorage) {
			$values = [];
		
			foreach ($value as $singleValue) {
				$singleValue = static::encodePropertyObject($singleValue);
				
				if ($singleValue !== null) {
					$values[] = $singleValue;
				}
			}
		
			$value = $values;
		}
		
		return $value;
	}
	
	protected static function encodePropertyObject(object $object): mixed
	{
		$value = null;
		
		if ($object->getUid() > 0) {
			$value = $object->getUid();
		
		} elseif ($object instanceof EncodeDecodeInterface) {
			$value = $object->encode();
		}
		
		return $value;
	}
	
	public static function decode(object $object, string $encodedData, array $properties = null): void
	{
		if ($properties === null && $object instanceof EncodeDecodeInterface) {
			$properties = $object->getEncodeProperties();
		}
		
		$data = json_decode($encodedData, true) ?? [];
		
		foreach ($properties as $property) {
			if (array_key_exists($property, $data)) {
				if ($object instanceof EncodeDecodeInterface) {
					$object->decodeProperty($property, $data[$property]);
				
				} else {
					static::decodeProperty($object, $property, $data[$property]);
				}
			}
		}
	}
	
	public static function decodeProperty(object $object, string $property, $encodedValue): void
	{
		$property = static::getProperty(get_class($object), $property);
		$value = $encodedValue;
		
		if ($property instanceof Property) {
			$value = static::decodePropertyObject($encodedValue, $property->getType(), $property->getElementType());
		}
		
		$object->{'set'. ucfirst($property)}($value);
	}
	
	protected static function decodePropertyObject($encodedObject, string $className, string $subclassName = null)
	{
		$object = null;
		
		if (is_a($className, AbstractEntity::class, true)) {
			if (MathUtility::canBeInterpretedAsInteger($encodedObject)) {
				$persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
				$object = $persistenceManager->getObjectByIdentifier((int) $encodedObject, $className);
				
			} elseif (is_a($className, EncodeDecodeInterface::class, true)) {
				$object = GeneralUtility::makeInstance($className);
				$object->decode($encodedObject);
			}
			
		} elseif (is_a($className, ObjectStorage::class, true)) {
			$objectStorage = new ObjectStorage();
			
			if (!is_array($encodedObject)) {
				$encodedObject = explode(',', $encodedObject);
			}
			
			foreach ($encodedObject as $encodedSingleObject) {
				$objectStorageChildObject = static::decodePropertyObject($encodedSingleObject, $subclassName);
				
				if ($objectStorageChildObject instanceof $subclassName) {
					$objectStorage->attach($objectStorageChildObject);
				}
			}
			
			$object = $objectStorage;
			
		} else {
			$object = $encodedObject;
		}
		
		return $object;
	}
}