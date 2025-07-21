<?php

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2023 STUDIO ERIGO, s.r.o.
 */

$EM_CONF[$_EXTKEY] = [
	'title' => 'ERIGO. Base',
	'description' => 'Sada nástrojů pro ERIGO web a moduly.',
	'category' => 'plugin',
	'author' => 'STUDIO ERIGO',
	'author_company' => 'STUDIO ERIGO, s.r.o.',
	'author_email' => 'studio@erigo.cz',
	'dependencies' => 'extbase,fluid',
	'state' => 'stable',
	'clearCacheOnLoad' => true,
	'version' => '13.0.0', // Aktualizováno na verzi 13
	'constraints' => [
		'depends' => [
			'typo3' => '13.0.0-13.99.99', // Aktualizováno pro TYPO3 13
			'vhs' => '7.0.0-7.99.99', // VHS je kompatibilní s TYPO3 13
		],
		'conflicts' => [],
		'suggests' => [],
	],
];