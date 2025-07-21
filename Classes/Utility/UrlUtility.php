<?php

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UrlUtility
{
	const TYPE_NUM_AJAX_PLUGIN = 3216000;
	const TYPE_NUM_AJAX_CONTENT = 3216010;
	const TYPE_NUM_AJAX_CONTENT_NO_TEMPLATE = 3216011;
	
    public static function sanitizeString(
        string $string, 
        array $configuration = [], 
        bool $replaceSlashes = true,
    ): string 
    {
        $configuration = array_replace(['prependSlash' => false, 'fallbackCharacter' => '-'], $configuration);
        
        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'none', 'none', $configuration);
        
        if ($replaceSlashes) {
            $string = str_replace('/', $configuration['fallbackCharacter'], $string);
        }
        
        return $slugHelper->sanitize($string);
    }
    
    public static function getFilterValues(array $filter): array
    {
		$filterValues = [];

		foreach ($filter as $field => $value) {
			if (is_array($value)) {
				if (array_key_exists('from', $value) && array_key_exists('to', $value)) {
					// range
					
					if ($value['from'] != '') {
						$filterValues[$field]['from'] = $value['from'];
					}
					
					if ($value['to'] != '') {
						$filterValues[$field]['to'] = $value['to'];
					}
					
				} else {
					// multiple values
					
					$fieldValues = [];
					
					foreach ($value as $singleValue) {
						if ($singleValue != '') {
							$fieldValues[] = $singleValue;
						}
					}
					
					if (count($fieldValues) > 0) {
						$filterValues[$field] = implode(',', $fieldValues);
					}
				}
				
			} else {
				if ($value != '') {
					$filterValues[$field] = $value;
				}
			}
		}
    	
    	return $filterValues;
    }
}