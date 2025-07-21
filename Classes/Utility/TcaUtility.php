<?php

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

use TYPO3\CMS\Backend\Form\FormDataProvider;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\{QueryHelper, Restriction};
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class TcaUtility
{
	public static function getNewTableConfig(
	    string $extKey, 
	    string $table, 
	    array $options = [],
    ): array 
    {
		$options = array_replace([
			'language' => true,
			'deleted' => true,
			'hidden' => true,
			'start' => true,
			'end' => true,
			'groups' => true,
		], $options);
		
		$config = [
			'ctrl' => [
				'title' => static::getLabel($table, $extKey),
				'label' => 'title',
				'tstamp' => 'tstamp',
				'crdate' => 'crdate',
				'sortby' => 'sorting',
				'default_sortby' => 'ORDER BY sorting ASC',
				'iconfile' => 'EXT:'. $extKey .'/Resources/Public/Icons/Tables/'. $table .'.svg',
			],
		    
			'types' => [
				'0' => [],
			],
		    
			'palettes' => [],
		    
			'columns' => [
				'pid' => [
					'config' => [
						'type' => 'passthrough',
					],
				],
			    
				'tstamp' => [
					'displayCond' => 'REC:NEW:false',
					'label' => static::getLabel('LGL.timestamp', 'core', 'general'),
					'config' => [
    					'type' => 'datetime',
    					'format' => 'datetime',
						'default' => 0,
						'size' => 15,
						'readOnly' => true,
					],
				],
			    
				'crdate' => [
					'displayCond' => 'REC:NEW:false',
					'label' => static::getLabel('LGL.creationDate', 'core', 'general'),
					'config' => [
    					'type' => 'datetime',
    					'format' => 'datetime',
						'default' => 0,
						'size' => 15,
						'readOnly' => true,
					],
				],
			],
		];
		
		$showitemFields = [];
		$paletteLanguageVisibilityFields = [];
		$paletteAccessFields = [];

		if ($options['language']) {
			$config['ctrl']['languageField'] = 'sys_language_uid';
			$config['ctrl']['transOrigPointerField'] = 'l10n_parent';
			$config['ctrl']['transOrigDiffSourceField'] = 'l10n_diffsource';
			
			$config['columns']['sys_language_uid'] = [
				'exclude' => true,
				'label' => static::getLabel('LGL.language', 'core', 'general'),
				'config' => [
					'type' => 'language',
				],
			];
			
			$config['columns']['l10n_parent'] = [
				'displayCond' => 'FIELD:sys_language_uid:>:0',
				'label' => static::getLabel('LGL.l18n_parent', 'core', 'general'),
				'config' => [
					'type' => 'select',
					'renderType' => 'selectSingle',
					'items' => [
						[
						    'label' => '',
						    'value' => 0,
		                ],
					],
					'foreign_table' => $table,
					'foreign_table_where' => 	'AND '. $table .'.pid = ###CURRENT_PID### '.
												'AND '. $table .'.sys_language_uid IN (-1,0)',
					'default' => 0,
				],
			];
			
			$config['columns']['l10n_diffsource'] = [
				'config' => [
					'type' => 'passthrough',
				],
			];
			
			$paletteLanguageVisibilityFields[] = 'sys_language_uid';
			$paletteLanguageVisibilityFields[] = 'l10n_parent';
		}
		
		if ($options['deleted']) {
			$config['ctrl']['delete'] = 'deleted';
		}
		
		if ($options['hidden']) {
			$config['ctrl']['enablecolumns']['disabled'] = 'hidden';
			
			$config['columns']['hidden'] = [
				'exclude' => true,
				'label' => static::getLabel('view', 'core', 'common'),
				'config' => [
					'type' => 'check',
					'renderType' => 'checkboxToggle',
					'items' => [
						[
						    'label' => '',
							'invertStateDisplay' => true,
						],
					],
				],
			];

			$paletteLanguageVisibilityFields[] = 'hidden';
		}
		
		if ($options['start']) {
			$config['ctrl']['enablecolumns']['starttime'] = 'starttime';
			
			$config['columns']['starttime'] = [
				'exclude' => true,
				'label' => static::getLabel('starttime_formlabel', 'frontend', 'ttc'),
				'config' => [
					'type' => 'datetime',
					'format' => 'datetime',
					'default' => 0,
				],
				'l10n_mode' => 'exclude',
				'l10n_display' => 'defaultAsReadonly',
			];

			$paletteAccessFields[] = 'starttime';
		}
		
		if ($options['end']) {
			$config['ctrl']['enablecolumns']['endtime'] = 'endtime';
			
			$config['columns']['endtime'] = [
				'exclude' => true,
				'label' => static::getLabel('endtime_formlabel', 'frontend', 'ttc'),
				'config' => [
					'type' => 'datetime',
					'format' => 'datetime',
					'default' => 0,
				],
				'l10n_mode' => 'exclude',
				'l10n_display' => 'defaultAsReadonly',
			];

			$paletteAccessFields[] = 'endtime';
		}
		
		if ($options['groups']) {
			$config['ctrl']['enablecolumns']['fe_group'] = 'fe_group';
			
			$config['columns']['fe_group'] = [
				'exclude' => true,
				'label' => static::getLabel('fe_group_formlabel', 'frontend', 'ttc'),
				'config' => [
					'type' => 'select',
					'renderType' => 'selectMultipleSideBySide',
					'foreign_table' => 'fe_groups',
					'foreign_table_where' => 	'ORDER BY fe_groups.title',
					'items' => [
					    [
					        'label' => static::getLabel('LGL.hide_at_login', 'core', 'general'),
					        'value' => -1,
					    ],
					    [
					        'label' => static::getLabel('LGL.any_login', 'core', 'general'),
					        'value' => -2,
					    ],
					    [
					        'label' => static::getLabel('LGL.usergroup', 'core', 'general'),
					        'value' => '--div--',
					    ],
					],
					'exclusiveKeys' => '-1,-2',
					'maxitems' => 100,
				],
			];

			$paletteAccessFields[] = 'fe_group';
		}

		if (count($paletteLanguageVisibilityFields) > 0) {
			$config['palettes']['language_visibility'] = [
				'showitem' => implode(', ', $paletteLanguageVisibilityFields),
			];
		}

		if (count($paletteAccessFields) > 0) {
			$config['palettes']['access'] = [
				'showitem' => implode(', ', $paletteAccessFields),
			];
		}

		return $config;
	}

	public static function getLabel(
	    string $key, 
	    string $extKey = 'erigo_base', 
	    string $fileSuffix = 'be', 
	    string $filename = 'locallang', 
	    string $folder = '/Resources/Private/Language/',
    ): string 
    {
		if ($fileSuffix != '') {
			$fileSuffix = '_'. $fileSuffix;
		}
		
		return 'LLL:EXT:'. $extKey . $folder . $filename . $fileSuffix .'.xlf:'. $key;
	}

	public static function getTranslatedLabel(
	    string $key, 
	    string $extKey = 'erigo_base', 
	    string $fileSuffix = 'be', 
	    ?array $arguments = null, 
	    string $filename = 'locallang', 
	    string $folder = '/Resources/Private/Language/',
    ): ?string 
    {
	    return LocalizationUtility::translate(
	        static::getLabel($key, $extKey, $fileSuffix, $filename, $folder), 
	        null, 
	        $arguments,
        );
	}
	
	public static function getFirstUid(mixed $uid): int
	{
		return (int) static::getFirstSelectedItem($uid);
	}
	
	public static function getFirstSelectedItem(mixed $selectedItem): mixed
	{
		if (is_array($selectedItem) && count($selectedItem) > 0) {
			$selectedItem = $selectedItem[0];
		}
		
		return $selectedItem;
	}
	
	public static function keepOnlySelectedItem(array &$params, mixed $selectedItem): void
	{
		$newItems = [];
		
		foreach ($params['items'] as $item) {
		    // V TYPO3 v13 mohou být items v různých formátech
		    $itemValue = is_array($item) && isset($item['value']) ? $item['value'] : ($item[1] ?? null);
		    
			if ($itemValue == $selectedItem) {
				$newItems[] = $item;
				break;
			}
		}
		
		$params['items'] = $newItems;
	}
	
	public static function getItems(
	    string $table, 
	    string $column, 
	    int $pid = 0, 
	    array $rowData = [], 
		bool $keepEmptyValue = false,
    ): array 
    {
		if (
		    !array_key_exists($table, $GLOBALS['TCA']) || 
		    !array_key_exists($column, $GLOBALS['TCA'][$table]['columns'])
		) {
			return [];
		}

		$tcaItems = [];
		$tcaSettings = $GLOBALS['TCA'][$table]['columns'][$column];
			
		if ($tcaSettings['config']['type'] == 'select') {
		// static items
			$tcaItems = $tcaSettings['config']['items'] ?? [];
			
		// empty values - normalizace formátu pro TYPO3 v13
			if (!$keepEmptyValue) {
				$notEmptyTcaItems = [];
				
				foreach ($tcaItems as $tcaItem) {
				    // Podpora různých formátů TCA items
				    $value = is_array($tcaItem) && isset($tcaItem['value']) 
				        ? $tcaItem['value'] 
				        : ($tcaItem[1] ?? '');
				        
					if (!empty($value)) {
						$notEmptyTcaItems[] = $tcaItem;
					}
				}
				
				$tcaItems = $notEmptyTcaItems;
			}
			
		// permissions (only for static items)
			if (array_key_exists('authMode', $tcaSettings['config'])) {
				$allowedTcaItems = [];
				$beUserAuth = $GLOBALS['BE_USER'];
				
				foreach ($tcaItems as $tcaItem) {
				    $value = is_array($tcaItem) && isset($tcaItem['value']) 
				        ? $tcaItem['value'] 
				        : ($tcaItem[1] ?? '');
				        
					if ($beUserAuth->checkAuthMode($table, $column, $value)) {
						$allowedTcaItems[] = $tcaItem;
					}
				}
				
				$tcaItems = $allowedTcaItems;
			}
			
		// foreign table
			if (array_key_exists('foreign_table', $tcaSettings['config'])) {
				$tableItems = static::getTableItems(
					$tcaSettings['config']['foreign_table'],
					array_key_exists('foreign_table_where', $tcaSettings['config']) ? 
					    $tcaSettings['config']['foreign_table_where'] : null,
					$pid,
				);
				
				foreach ($tableItems as $tcaItem) {
					$tcaItems[] = $tcaItem;
				}
			}
			
		// specials
			if (array_key_exists('special', $tcaSettings['config'])) {
				switch ($tcaSettings['config']['special']) {
					case 'languages':
						foreach (static::getSpecialLanguageItems($pid) as $tcaItem) {
							$tcaItems[] = $tcaItem;
						}
						break;
				}
			}
			
		// user function
			if (array_key_exists('itemsProcFunc', $tcaSettings['config'])) {
				$processorParams = [
					'items' => &$tcaItems,
					'table' => $table,
					'row' => $rowData,
				];
					
				GeneralUtility::callUserFunction(
					$tcaSettings['config']['itemsProcFunc'], 
					$processorParams, 
					GeneralUtility::makeInstance(FormDataProvider\TcaSelectItems::class),
				);
			}
		}
	
		return $tcaItems;
	}
	
	public static function getTableItems(string $table, string $where = null, int $pid = 0): array
	{
		$tableItems = [];
		
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
		
		$queryBuilder->getRestrictions()->removeAll()
			->add(GeneralUtility::makeInstance(Restriction\DeletedRestriction::class));
		
		$queryBuilder
			->select('*')
			->from($table);
			
		if (!empty($where)) {
			$where = str_replace('###THIS_UID###', '0', $where);
			$where = str_replace('###CURRENT_PID###', (string)$pid, $where);
			$where = preg_replace('/= ###REC_FIELD_[a-z0-9_]+###/', "LIKE '%'", $where);
			$where = preg_replace('/IN \(###REC_FIELD_[a-z0-9_]+###\)/', "LIKE '%'", $where);
				
			$whereParts = GeneralUtility::trimExplode('ORDER BY', $where, false, 2);
			$where = $whereParts[0];
				
			if (!empty($where)) {
				$queryBuilder->andWhere(
					QueryHelper::stripLogicalOperatorPrefix(
						QueryHelper::quoteDatabaseIdentifiers($queryBuilder->getConnection(), $where),
					),
				);
			}
				
			if (count($whereParts) > 1) {
				foreach (QueryHelper::parseOrderBy($whereParts[1]) as $orderByIndex => $orderByPart) {
					if ($orderByIndex == 0) {
						$queryBuilder->orderBy($orderByPart[0], $orderByPart[1]);
		
					} else {
						$queryBuilder->addOrderBy($orderByPart[0], $orderByPart[1]);
					}
				}
			}
		}
		
		// V TYPO3 v13 se používá executeQuery() místo execute()
		$result = $queryBuilder->executeQuery();

		$tca = $GLOBALS['TCA'][$table];
		$labelColumn = $tca['ctrl']['label'];
		
		if ($tca['columns'][$labelColumn]['config']['type'] == 'select') {
			$tca['columns'][$labelColumn]['config']['items'] = static::getItems($table, $labelColumn, $pid, [], true);
		}
		
		// V TYPO3 v13 se používá fetchAssociative() místo fetch()
		while ($row = $result->fetchAssociative()) {
			if ($tca['columns'][$labelColumn]['config']['type'] == 'select' && !is_array($row[$labelColumn])) {
				$row[$labelColumn] = GeneralUtility::trimExplode(',', $row[$labelColumn], true);
			}
			
			$result = [
				'processedTca' => $tca,
				'tableName' => $table,
				'databaseRow' => $row,
			];
			
			$recordTitleProvider = GeneralUtility::makeInstance(FormDataProvider\TcaRecordTitle::class);
			$result = $recordTitleProvider->addData($result);
				
			// Normalizace formátu pro TYPO3 v13
			$tableItems[] = [
			    'label' => $result['recordTitle'], 
			    'value' => $row['uid'],
			];
		}
		
		return $tableItems;
	}
	
	protected static function getSpecialLanguageItems(int $pid = 0): array
	{
		$languageItems = [];
		
		if (isset($GLOBALS['TYPO3_REQUEST'])) {
		    $applicationType = ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST']);
		    
		    if ($applicationType->isBackend()) {
			    try {
				    $site = GeneralUtility::makeInstance(Site\SiteFinder::class)->getSiteByPageId($pid);
				    
				    foreach ($site->getAvailableLanguages($GLOBALS['BE_USER'], true, $pid) as $language) {
					    if ($language->getLanguageId() >= 0) {
						    // Normalizace formátu pro TYPO3 v13
						    $languageItems[] = [
							    'label' => $language->getTitle(),
							    'value' => $language->getLanguageId(),
							    'icon' => $language->getFlagIdentifier(),
						    ];
					    }
				    }
				    
			    } catch (SiteNotFoundException $e) {}
		    }
		}
		
		return $languageItems;
	}
}