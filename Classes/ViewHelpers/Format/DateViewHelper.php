<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Erigo\ErigoBase\Utility\IntlUtility;

class DateViewHelper extends AbstractViewHelper
{
	/** @var bool */
	protected $escapeOutput = false;
	
	public function initializeArguments(): void
	{
		$this->registerArgument('date', 'mixed', 'Date to format.', false, null);
		
		$this->registerArgument(
		    'dateFormat',
		    'string',
		    'Date format as constant from IntlUtility class.',
		    false, 
			IntlUtility::DATE_FORMAT_MEDIUM,
	    );
		
		$this->registerArgument(
		    'timeFormat',
		    'string',
		    'Time format as constant from IntlUtility class.',
		    false, 
			IntlUtility::DATE_FORMAT_SHORT,
	    );
	}
	
	public function render(): string
	{
		$date = $this->arguments['date'];
		if ($date === null) {
			$date = $this->renderChildren();
		}
		
		return IntlUtility::formatDate($date, $this->arguments['dateFormat'], $this->arguments['timeFormat']);
	}
}