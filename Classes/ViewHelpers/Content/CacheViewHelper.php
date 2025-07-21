<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers\Content;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Erigo\ErigoBase\Service\ContentCacheService;

class CacheViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('data', 'array', 'Data of content element.', true);
	}
	
	public function render(): mixed
	{
		$contentCacheService = GeneralUtility::makeInstance(ContentCacheService::class);
		
		return $contentCacheService->add($this->arguments['data']);
	}
}