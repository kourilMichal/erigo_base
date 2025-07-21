<?php

defined('TYPO3') || die();

return [
    'ctrl' => [
        'title' => 'Parametr',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'title',
        'iconfile' => 'EXT:erigo_base/Resources/Public/Icons/sys_param.svg',
    ],
    'types' => [
        '0' => [
            'showitem' => 'hidden, title, slug, type, input_mode, default_value',
        ],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'Skrýt',
            'config' => [
                'type' => 'check',
            ],
        ],
        'title' => [
            'exclude' => false,
            'label' => 'Název parametru',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,required',
            ],
        ],
        'sorting' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'slug' => [
            'exclude' => true,
            'label' => 'Slug',
            'config' => [
                'type' => 'slug',
                'generatorOptions' => [
                    'fields' => ['title'],
                    'fieldSeparator' => '-',
                ],
                'eval' => 'uniqueInSite',
            ],
        ],
        'type' => [
            'exclude' => false,
            'label' => 'Typ parametru',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Text', 'text'],
                    ['Číslo', 'number'],
                    ['Datum', 'date'],
                    ['Ano/Ne', 'boolean'],
                ],
                'default' => 'text',
                'onChange' => 'reload',
            ],
        ],
        'input_mode' => [
            'exclude' => true,
            'label' => 'Způsob zadávání',
            'displayCond' => 'FIELD:type:IN:text,number,date,boolean',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Text Input', 'input'],
                    ['Víceřádkový text (textarea)', 'textarea'],
                    ['RTE (Rich text)', 'rte'],
                    ['Celé číslo', 'integer'],
                    ['Desetinné číslo', 'decimal'],
                ],
                'default' => '',
            ],
        ],
        'default_value' => [
            'exclude' => true,
            'label' => 'Výchozí hodnota',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        
        // (nepovinné) jazykové sloupce
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages', -1],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.default_value', 0]
                ],
                'default' => 0,
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l10n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_param',
                'foreign_table_where' => 'AND sys_param.pid=###CURRENT_PID### AND sys_param.sys_language_uid IN (-1,0)',
                'default' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];
