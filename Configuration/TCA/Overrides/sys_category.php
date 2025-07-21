<?php 

defined('TYPO3') || die();

(function ($extKey, $table) {
	
	$optionFields = 'shortcut';
	if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('news')) {
		$optionFields .= 'single_pid, '. $optionFields;
	}
	

	##########
	## CTRL ##
	##########
	
	if (array_key_exists('descriptionColumn', $GLOBALS['TCA'][$table]['ctrl'])) {
		unset($GLOBALS['TCA'][$table]['ctrl']['descriptionColumn']);
	}
	

	###########
	## Types ##
	###########
	
	$GLOBALS['TCA'][$table]['types'] = array_replace_recursive(
		$GLOBALS['TCA'][$table]['types'],
		[
			'1' => [
				'showitem' => 	'--palette--;;language_visibility, --palette--;;time_restriction, '.
			                    '--palette--;;title_slug, parent, description, '.
							'--div--;'. \Erigo\ErigoBase\Utility\TcaUtility::getLabel('tca.tab.media') .', '.
								'images, '.
							'--div--;'. \Erigo\ErigoBase\Utility\TcaUtility::getLabel(
									'pages.tabs.options', 'frontend', 'tca'
								) .', '.
								$optionFields .
            				'--div--;'. \Erigo\ErigoBase\Utility\TcaUtility::getLabel('tca.tab.synch') .', '.
            					'--palette--;;import_data, synch_history',
			],
		],
	);
	
	
	##############
	## Palettes ##
	##############
	
	$GLOBALS['TCA'][$table]['palettes'] = array_replace_recursive(
		$GLOBALS['TCA'][$table]['palettes'],
		[
			'language_visibility' => [
				'showitem' => 	'sys_language_uid, l10n_parent, hidden',
			],
		    
			'time_restriction' => [
				'showitem' => 	'starttime, endtime',
			],
		    
			'title_slug' => [
				'showitem' => 	'title, slug',
			],
		    
			'import_data' => [
				'showitem' => 	'import_id, import_source',
			],
		],
	);
			
	
	#############
	## Columns ##
	#############
	
	$GLOBALS['TCA'][$table]['columns'] = array_replace_recursive(
		$GLOBALS['TCA'][$table]['columns'],
		[
		// overrides
			'description' => ['config' => ['enableRichtext' => true]],
			
			'parent' => [
				'config' => [
					'foreign_table_where' => 	' AND {#'. $table .'}.{#sys_language_uid} IN (-1, 0)'.
												' ORDER BY '. $table .'.sorting ASC',
				],
				'l10n_mode' => 'exclude',
			],
		    
			'synch_history' => [
				'exclude' => true,
				'displayCond' => 'REC:NEW:false',
				'label' => \Erigo\ErigoBase\Utility\TcaUtility::getLabel('tca.field.synch_history'),
				'config' => [
					'type' => 'user',
					'renderType' => 'synchHistoryElement',
				],
			],
		],
	);
	
	if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('news')) {
		$GLOBALS['TCA'][$table]['columns'] = array_replace_recursive(
			$GLOBALS['TCA'][$table]['columns'],
			[
			// overrides
				'images' => ['label' => \Erigo\ErigoBase\Utility\TcaUtility::getLabel('tca.field.images')],
			
				'import_id' => [
					'exclude' => true,
					'displayCond' => 'REC:NEW:false',
					'label' => \Erigo\ErigoBase\Utility\TcaUtility::getLabel('tca.field.import_id'),
					'config' => [
						'type' => 'input',
						'eval' => 'trim',
						'size' => 15,
						'readOnly' => true,
					],
				],
			    
				'import_source' => [
					'exclude' => true,
					'displayCond' => 'REC:NEW:false',
					'label' => \Erigo\ErigoBase\Utility\TcaUtility::getLabel('tca.field.import_source'),
					'config' => [
						'type' => 'input',
						'eval' => 'trim',
						'size' => 15,
						'readOnly' => true,
					],
					'l10n_mode' => 'exclude',
					'l10n_display' => 'defaultAsReadonly',
				],
			],
		);
		
	} else {
		$GLOBALS['TCA'][$table]['columns'] = array_replace_recursive(
			$GLOBALS['TCA'][$table]['columns'],
			[
			// new fields
				'pid' => [
					'config' => [
						'type' => 'passthrough',
					],
				],
			    
				'sorting' => [
					'config' => [
						'type' => 'passthrough',
					],
				],
			    
				'crdate' => [
					'config' => [
						'type' => 'passthrough',
					],
				],
			    
				'tstamp' => [
					'config' => [
						'type' => 'passthrough',
					],
				],
			    
				'slug' => [
					'exclude' => true,
					'label' => \Erigo\ErigoBase\Utility\TcaUtility::getLabel('pages.slug', 'core', 'tca'),
					'config' => [
						'type' => 'slug',
						'generatorOptions' => [
							'fields' => ['title'],
							'fieldSeparator' => '-',
							'replacements' => [
								'/' => '-',
							],
						],
						'fallbackCharacter' => '-',
						'eval' => 'uniqueInSite',
					],
				],
			    
				'images' => [
					'exclude' => true,
					'label' => \Erigo\ErigoBase\Utility\TcaUtility::getLabel('tca.field.images'),
					'config' => [
						'type' => 'file',
						'maxitems' => 999,
						'allowed' => 'common-image-types',
				    ],
				],
			    
				'import_id' => [
					'exclude' => true,
					'displayCond' => 'REC:NEW:false',
					'label' => \Erigo\ErigoBase\Utility\TcaUtility::getLabel('tca.field.import_id'),
					'config' => [
						'type' => 'input',
						'eval' => 'trim',
						'size' => 15,
						'readOnly' => true,
					],
				],
			    
				'import_source' => [
					'exclude' => true,
					'displayCond' => 'REC:NEW:false',
					'label' => \Erigo\ErigoBase\Utility\TcaUtility::getLabel('tca.field.import_source'),
					'config' => [
						'type' => 'input',
						'eval' => 'trim',
						'size' => 15,
						'readOnly' => true,
					],
					'l10n_mode' => 'exclude',
					'l10n_display' => 'defaultAsReadonly',
				],
			],
		);
	}
	
})(
	'erigo_base', 
	'sys_category',
);