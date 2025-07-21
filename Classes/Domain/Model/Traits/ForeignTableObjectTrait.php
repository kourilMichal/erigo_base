<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

trait ForeignTableObjectTrait
{
	protected string $foreignTable;
	protected int $foreignObject;

	public function getForeignTable(): string
	{
		return $this->foreignTable;
	}
	
	public function setForeignTable(string $foreignTable): void
	{
		$this->foreignTable = $foreignTable;
	}
	
	public function getForeignObject(): int
	{
		return $this->foreignObject;
	}
	
	public function setForeignObject(int $foreignObject): void
	{
		$this->foreignObject = $foreignObject;
	}
}