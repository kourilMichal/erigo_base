<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Form;

use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;

interface FormValueFormatProviderInterface
{
	public function formatValue(FormElementInterface $formElement, mixed $value, mixed $processedValue): mixed;
}