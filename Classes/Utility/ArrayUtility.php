<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2022 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

class ArrayUtility
{
    public static function findByDotSeparatedKey(array $array, string $key, mixed $defaultValue = null): mixed
    {
		$keyParts = explode('.', $key);
		$currentArray = $array;
		
		foreach ($keyParts as $keyPart) {
			if (array_key_exists($keyPart, $currentArray)) {
				$currentArray = $currentArray[$keyPart];
		
			} else {
				return $defaultValue;
			}
		}
		
		return $currentArray;
    }
}