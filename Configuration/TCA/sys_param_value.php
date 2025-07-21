<?php

defined('TYPO3') || die();

use Erigo\ErigoBase\TCA\ParamHelper;

return [
    'ctrl' => [
        'title' => 'Parametry hodnot',
        'label' => 'param',
        'label_userFunc' => ParamHelper::class . '->getValueField',
        'hideTable' => true,
        'requestUpdate' => 'param',
    ],
    'types' => [
        '0' => [
            'showitem' => '--palette--;;param_value',
        ],
    ],
    'palettes' => [
        'param_value' => [
            'showitem' => 'param, value_field',
        ],
    ],
    'columns' => [
        'param' => [
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_param',
                'foreign_table_where' => 'ORDER BY sys_param.title ASC',
                'onChange' => 'reload',
                'itemsProcFunc' => ParamHelper::class . '->getParamOptions',
            ],
        ],
        'foreign_table' => [
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
                'default' => '',
            ],
        ],
        'foreign_object' => [
            'exclude' => true,
            'label' => 'Foreign Object',
            'config' => [
                'type' => 'input',
                'eval' => 'int',
                'size' => 10,
                'default' => 0,
            ],
        ],
        'value_field' => [
            'label' => 'Hodnota parametru',
            'config' => [
                'type' => 'user',
                'renderType' => 'input',
                'userFunc' => ParamHelper::class . '->getValueFieldType',
            ],
        ],
    ],
];
