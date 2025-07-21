<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Provider\Backend;

use Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface;
use Erigo\ErigoBase\Filter\Provider\AbstractNumberOptionsFilterProvider;

class NumberFilterProvider extends AbstractNumberOptionsFilterProvider
{
	/**
	 * @see \Erigo\ErigoBase\Filter\Provider\AbstractFilterProvider::getFormElement()
	 */
	public function getFormElement(): string
	{
		return FilterProviderInterface::FORM_ELEMENT_NUMBER;
	}
	
	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\NumberOptionsFilterProviderInterface::getValuesRange()
	 */
	public function getValuesRange(): array
	{
		return [];
	}
}