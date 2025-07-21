<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

return [
	'frontend' => [
		'erigo/base/api' => [
			'target' => \Erigo\ErigoBase\Middleware\ApiMiddleware::class,
			'before' => [
				'typo3/cms-frontend/page-resolver',
			],
		],
	],
];