<?php 

defined('TYPO3') || die();

(function ($extKey, $table) {

	$tableConfig = \Erigo\ErigoBase\Utility\TcaUtility::getNewTableConfig($extKey, $table, ['language' => false]);
	
	$ttContentFilter = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Erigo\ErigoBase\TCA\FilterHelper::class);
	
	$GLOBALS['TCA'][$table] = array_replace_recursive($tableConfig, [
		'ctrl' => [
			'label' => 'title',
			'label_alt' => 'property',
			'searchFields' => 'plugin, property, title',
			'hideTable' => true,
		],
	    
		'types' => [
			'0' => [
				'showitem' => 	'--palette--;;title_slug, property, settings',
			],
		],
	    
		'palettes' => [
		    'title_slug' => [
				'showitem' => 'title, slug',
			],
		],
	    
		'columns' => [
			'sorting' => [
				'config' => [
					'type' => 'passthrough',
				],
			],
		    
			'content_uid' => [
				'config' => [
					'type' => 'passthrough',
				],
			],
		    
			'plugin' => [
				'config' => [
					'type' => 'passthrough',
				],
			],
		    
			'property' => [
				'label' => \Erigo\ErigoBase\Utility\TcaUtility::getLabel($table .'.property', $extKey),
				'config' => [
					'type' => 'select',
					'renderType' => 'selectSingle',
					'items' => [
						[
						    'value' => '', 
						    'label' => '',
						],
					],
					'itemsProcFunc' => \Erigo\ErigoBase\TCA\FilterHelper::class .'->getPropertyOptions',
				],
				'onChange' => 'reload',
				'l10n_mode' => 'exclude', // ???
			],
		    
			'title' => [
				'label' => \Erigo\ErigoBase\Utility\TcaUtility::getLabel('LGL.title', 'core', 'general'),
				'config' => [
					'type' => 'input',
					'eval' => 'trim',
				],
			],
		    
			'slug' => [
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
					'eval' => 'uniqueInPid',
				],
			],
		    
			'settings' => [
				'displayCond' => 'FIELD:property:REQ:true',
				'label' => \Erigo\ErigoBase\Utility\TcaUtility::getLabel($table .'.settings', $extKey),
				'config' => [
					'type' => 'flex',
					'ds_pointerField' => 'plugin,property',
					'ds' => $ttContentFilter->getSettingsOptions(),
				],
				'l10n_mode' => 'exclude', // ???
			],
		],
	]);

})(
	'erigo_base',
	'tt_content_filter',
);