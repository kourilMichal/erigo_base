<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2022 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Service;

use TYPO3\CMS\Core\SingletonInterface;

interface ImageServiceInterface extends SingletonInterface
{
	const WIDTH_TYPE_INNER = 'inner';
	const WIDTH_TYPE_OUTER = 'outer';
    
	public function getResponsiveVariants(
	    array $contentData, 
	    array $files, 
	    string $widthType, 
	    array $pluginOptions,
    ): array;
	
	public function getViewConfiguration(): array;
	
	public function isLazyLoadingEnabled(): bool;
}