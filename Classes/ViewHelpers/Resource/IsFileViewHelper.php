<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2022 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers\Resource;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class IsFileViewHelper extends AbstractConditionViewHelper
{
    use CompileWithRenderStatic;
    
	public function initializeArguments()
	{
		$this->registerArgument('filePath', 'string', 'File path.', true);
	}
	
	public static function verdict(array $arguments, RenderingContextInterface $renderingContext)
	{
	    return is_file(GeneralUtility::getFileAbsFileName(ltrim($arguments['filePath'], '/')));
	}
}