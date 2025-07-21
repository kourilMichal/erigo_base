<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers\Content;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;

class ResponsiveImageViewHelper extends AbstractResponsiveImagesViewHelper
{
	public function initializeArguments()
	{
		$this->registerArgument('file', 'object', 'File or file reference.', false, null);
		$this->registerArgument('src', 'string', 'Path to file.', false, null);
		
		parent::initializeArguments();
	}

	protected static function getFilesArray(array $arguments): array
	{
		$file = $arguments['file'];
		
		if ($file == null && $arguments['src'] != null) {
			$imageService = GeneralUtility::makeInstance(ImageService::class);
			
			$file = $imageService->getImage($arguments['src'], null, false);
		}
		
		return [$file];
	}
	
	protected static function getReturnResult(array $variants): array
	{
	    // Vyber správnou variantu na základě breakpointu
	    usort($variants, function ($a, $b) {
	        return $b['maxWidth'] <=> $a['maxWidth'];
	    });
	        
	        foreach ($variants as $variant) {
	            if ($variant['maxWidth'] >= 1800) {
	                return $variant; // Nejvyšší rozlišení pro xl breakpoint
	            }
	        }
	        
	        // Fallback: největší dostupná varianta
	        return $variants[0] ?? [];
	}
	
	
}