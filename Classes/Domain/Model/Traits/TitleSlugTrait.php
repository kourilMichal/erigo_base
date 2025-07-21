<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

trait TitleSlugTrait
{
	protected string $title = '';
	protected string $slug = '';

	public function getTitle(): string
	{
		return $this->title;
	}
	
	public function setTitle(string $title): void
	{
		$this->title = $title;
	}
	
	public function getSlug(): string
	{
		return $this->slug;
	}
	
	public function setSlug(string $slug): void
	{
		$this->slug = $slug;
	}
}