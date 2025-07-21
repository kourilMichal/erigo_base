<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Interfaces;

interface NumberOptionsFilterProviderInterface extends DbValueOptionsFilterProviderInterface
{
	const RANGE_TYPE_MANUAL = 'manual';
	const RANGE_TYPE_AUTO = 'auto';
	const RANGE_TYPE_ABOVE_ONLY = 'above_only';
	const RANGE_TYPE_BELOW_ONLY = 'below_only';
	
	public function getValuesRange(): array;
	
	public function getNumberOptions(float $minValue, float $maxValue): array;
}