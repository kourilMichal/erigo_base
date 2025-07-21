<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Provider\Backend;

use Erigo\ErigoBase\Filter\Interfaces\FilterProviderInterface;
use Erigo\ErigoBase\Filter\Provider\AbstractOptionsFilterProvider;

class OptionsFilterProvider extends AbstractOptionsFilterProvider
{
	/**
	 * @see \Erigo\ErigoBase\Filter\Provider\AbstractFilterProvider::getFormElement()
	 */
	public function getFormElement(): string
	{
		return FilterProviderInterface::FORM_ELEMENT_MULTISELECT;
	}
	
	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\OptionsFilterProviderInterface::getAllOptions()
	 */
	public function getAllOptions(): array
	{
		return $this->allOptions ?? [];
	}
	
	public function setAllOptions(array $allOptions): void
	{
		$this->allOptions = $allOptions;
	}
}