<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\TCA\FormElement;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Erigo\ErigoBase\Utility\IntlUtility;

abstract class AbstractCustomFormElement extends AbstractFormElement
{
	protected function wrapFieldOutput(string $itemOutput): string
	{
		$html = [];
		$html[] = '<div class="form-control-wrap">';
		$html[] =	 '<span class="form-control" readonly>';
		$html[] =		 $itemOutput;
		$html[] =	 '</span>';
		$html[] = '</div>';
		
		return implode(LF, $html);
	}
	
	protected function wrapOutput(string $output, $maxWidth = 0): string
	{
		$html = [];
		$html[] = '<div class="formengine-field-item t3js-formengine-field-item">';
		$html[] =	 '<div class="form-wizards-wrap"'.
		                     (($maxWidth > 0 ? ' style="max-width:'. $maxWidth .'px;"' : '')) .'>';
		$html[] =		 '<div class="form-wizards-element">';
		$html[] =			 $output;
		$html[] =		 '</div>';
		$html[] =	 '</div>';
		$html[] = '</div>';
		
		return implode(LF, $html);
	}
	
	protected function formatTimestamp(int $timestamp): string
	{
		return IntlUtility::formatDate($timestamp, IntlUtility::DATE_FORMAT_MEDIUM, IntlUtility::DATE_FORMAT_MEDIUM);
	}
	
	protected function translate(string $key, string $extKey = 'erigo_base', string $fileSuffix = 'be'): string
	{
		if ($fileSuffix != '') {
			$fileSuffix = '_'. $fileSuffix;
		}
		
		return LocalizationUtility::translate(
		    'LLL:EXT:'. $extKey .'/Resources/Private/Language/locallang'. $fileSuffix .'.xlf:'. $key
	    ) ?? '';	
	}
}