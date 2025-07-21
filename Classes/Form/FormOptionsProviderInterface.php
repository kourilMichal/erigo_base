<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Form;

use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

interface FormOptionsProviderInterface
{
	public function getOptions(FormElementInterface $formElement): array;
	
	public function getDefaultOption(FormElementInterface $formElement): mixed;
	
	public function getValue(
	    FormRuntime $formRuntime, 
	    FormElementInterface $formElement, 
	    mixed $elementValue, 
	    array $requestArguments = []
    ): mixed;

	public function shouldPreserveBackendOptions(): bool;
}