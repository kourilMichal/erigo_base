<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Interfaces;

interface TextFieldsProviderInterface extends FilterProviderInterface
{
	const FIELDS_MODE_ALL = 'all';
	const FIELDS_MODE_SELECTED = 'selected';
	
	public function getAllFields(): array;
	
	public function getAvailableFields(): array;
	
	public function getPlaceholder(): string;
}