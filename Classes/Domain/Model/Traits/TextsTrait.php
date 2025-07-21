<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

trait TextsTrait
{
	protected string $perex = '';
	protected string $text = '';

	public function getPerex(): string
	{
		return $this->perex;
	}
	
	public function setPerex(string $perex): void
	{
		$this->perex = $perex;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function setText(string $text): void
	{
		$this->text = $text;
	}
}