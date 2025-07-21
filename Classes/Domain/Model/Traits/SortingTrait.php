<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

trait SortingTrait
{
	protected int $sorting = 0;

	public function getSorting(): int
	{
		return $this->sorting;
	}
	
	public function setSorting(int $sorting): void
	{
		$this->sorting = $sorting;
	}
}