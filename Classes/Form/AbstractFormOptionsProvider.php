<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Form;

use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

abstract class AbstractFormOptionsProvider implements FormOptionsProviderInterface
{
	/**
	 * @see \Erigo\ErigoBase\Form\FormOptionsProviderInterface::getDefaultOption()
	 */
	public function getDefaultOption(FormElementInterface $formElement): mixed
	{
		return null;
	}

	/**
	 * @see \Erigo\ErigoBase\Form\FormOptionsProviderInterface::getValue()
	 */
	public function getValue(
	    FormRuntime $formRuntime, 
	    FormElementInterface $formElement, 
	    mixed $elementValue, 
	    array $requestArguments = []
    ): mixed
    {
		return $elementValue;
	}

	/**
	 * @see \Erigo\ErigoBase\Form\FormOptionsProviderInterface::shouldPreserveBackendOptions()
	 */
	public function shouldPreserveBackendOptions(): bool
	{
		return false;
	}
}