<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2024 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\EventListener\FlexForm;

use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureParsedEvent;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\{ArrayUtility, GeneralUtility};

class SheetImport
{
    public function __construct(
        protected FlexFormTools $flexFormTools,
    ) {}
    
    public function __invoke(AfterFlexFormDataStructureParsedEvent $event): void
    {
        $dataStructure = $event->getDataStructure();
        
        if (isset($dataStructure['sheets']) && is_array($dataStructure['sheets'])) {
            foreach ($dataStructure['sheets'] as $sheetName => $sheetStructure) {
                if (!is_array($sheetStructure)) {
                    continue;
                }
                
                if (array_key_exists('imports', $sheetStructure) && is_array($sheetStructure['imports'])) {
                    $rootItem = [];
                    if (array_key_exists('ROOT', $sheetStructure) && is_array($sheetStructure['ROOT'])) {
                        $rootItem = $sheetStructure['ROOT'];
                    }
                    
                    foreach ($sheetStructure['imports'] as $import) {
                        if (!array_key_exists('file', $import)) {
                            continue;
                        }
                        
                        $importFile = GeneralUtility::getFileAbsFileName($import['file']);
                        
                        if (!empty($importFile) && is_file($importFile)) {
                            $importStructure = GeneralUtility::xml2array((string) file_get_contents($importFile));
                            
                            if (is_array($importStructure)) {
                                if (array_key_exists('fixItems', $import) && $import['fixItems'] == 'true') {
                                    $importStructure = $this->fixItems($importStructure);
                                }
                                
                                if (array_key_exists('sheets', $importStructure)) {
                                    if (
                                        !array_key_exists('sheet', $import) ||
                                        !array_key_exists($import['sheet'], $importStructure['sheets'])
                                    ) {
                                        continue;
                                    }
                                    
                                    $importStructure = $importStructure['sheets'][$import['sheet']];
                                }
                                
                                if (
                                    array_key_exists('ROOT', $importStructure) &&
                                    is_array($importStructure['ROOT'])
                                ) {
                                    ArrayUtility::mergeRecursiveWithOverrule($rootItem, $importStructure['ROOT']);
                                }
                            }
                        }
                    
                        if (array_key_exists('removeTceForms', $import) && $import['removeTceForms'] == 'true') {
                            $newStructure = $this->flexFormTools->removeElementTceFormsRecursive([
                                'ROOT' => $rootItem,
                            ]);
                            
                            $rootItem = $newStructure['ROOT'];
                        }
                    }
                    
                    if (array_key_exists('overrides', $sheetStructure) && is_array($sheetStructure['overrides'])) {
                        if (!array_key_exists('el', $rootItem)) {
                            $rootItem['el'] = [];
                        }
                        
                        ArrayUtility::mergeRecursiveWithOverrule($rootItem['el'], $sheetStructure['overrides']);
                    }
                    
                    $dataStructure['sheets'][$sheetName]['ROOT'] = $rootItem;
                }
            }
        }
        
        $event->setDataStructure($dataStructure);
    }
    
    protected function fixItems(array $structure, ?string $lastKey = null): array
    {
        if (array_key_exists('items', $structure) && $lastKey == 'config') {
            $newItems = [];
            
            foreach ($structure['items'] as $item) {
                $newItem = [
                    'label' => $item[0],
                    'value' => $item[1],
                ];
                
                if (count($item) > 2) {
                    $newItem['icon'] = $item[2];
                }
                
                $newItems[] = $newItem;
            }
            
            $structure['items'] = $newItems;
            
        } else {
            foreach ($structure as $key => $value) {
                if (is_array($value)) {
                    $structure[$key] = $this->fixItems($value, $key);
                }
            }
        }
        
        return $structure;
    }
}