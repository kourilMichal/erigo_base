<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use Erigo\ErigoBase\Utility\IntlUtility;

class NumberViewHelper extends AbstractViewHelper
{
	use CompileWithRenderStatic;
	
	/** @var bool */
	protected $escapeOutput = false;
	
	public function initializeArguments()
	{
		$this->registerArgument('number', 'float', 'Number to format.', false, null);
		$this->registerArgument('decimals', 'int', 'The number of digits after the decimal point.');
	}
	
	public static function renderStatic(
	    array $arguments,
	    \Closure $renderChildrenClosure,
	    RenderingContextInterface $renderingContext,
    ): string
	{
		$number = $arguments['number'];
		if ($number === null) {
			$number = $renderChildrenClosure();
		}
		
		$attributes = [];
		
		if ($arguments['decimals'] !== null) {
			$attributes[\NumberFormatter::MAX_FRACTION_DIGITS] = $arguments['decimals'];
			$attributes[\NumberFormatter::MIN_FRACTION_DIGITS] = $arguments['decimals'];
		}
		
		return IntlUtility::formatNumber($number, $attributes);
	}
}