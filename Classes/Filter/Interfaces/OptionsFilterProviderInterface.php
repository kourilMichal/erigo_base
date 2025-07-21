<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Interfaces;

interface OptionsFilterProviderInterface extends FilterProviderInterface
{
	const OPTIONS_MODE_ALL = 'all';
	const OPTIONS_MODE_SELECTED = 'selected';
	
	public function getAllOptions(): array;
	
	public function getAvailableOptions(): array;
	
	public function isValidOption(mixed $value): bool;

	public function getDefaultOptionLabel(): string;
}