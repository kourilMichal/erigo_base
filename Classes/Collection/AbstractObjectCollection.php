<?php

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Collection;

abstract class AbstractObjectCollection implements \Iterator, \Countable
{
	protected int $position = 0;
	protected array $objects = [];
	
	public function rewind(): void
	{
		$this->position = 0;
	}
	
	public function current(): mixed
	{
		return $this->objects[$this->position];
	}
	
	public function key(): mixed
	{
		return $this->position;
	}
	
	public function next(): void
	{
		++$this->position;
	}
	
	public function valid(): bool
	{
		return isset($this->objects[$this->position]);
	}
	
	public function count(): int
	{
		return count($this->objects);
	}
	
	public function getObjects(): array
	{
		return $this->objects;
	}
}