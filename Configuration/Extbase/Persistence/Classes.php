<?php 

declare(strict_types = 1);

return [
	\Erigo\ErigoBase\Domain\Model\ContentFilter::class => [
		'tableName' => 'tt_content_filter',
	],

	\Erigo\ErigoBase\Domain\Model\FileReference::class => [
		'tableName' => 'sys_file_reference',
        'properties' => [
            'file' => ['fieldName' => 'uid_local'],
            'foreignObject' => ['fieldName' => 'uid_foreign'],
            'foreignTable' => ['fieldName' => 'tablenames'],
            'fieldName' => ['fieldName' => 'fieldname'],
            'localTable' => ['fieldName' => 'table_local'],
        ],
	],
];