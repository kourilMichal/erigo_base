<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

trait SeoTrait
{
	protected string $metaTitle = '';
	protected string $metaKeywords = '';
	protected string $metaDescription = '';

	public function getMetaTitle(): string
	{
		return $this->metaTitle;
	}
	
	public function setMetaTitle(string $metaTitle): void
	{
		$this->metaTitle = $metaTitle;
	}
	
	public function getMetaKeywords(): string
	{
		return $this->metaKeywords;
	}
	
	public function setMetaKeywords(string $metaKeywords): void
	{
		$this->metaKeywords = $metaKeywords;
	}
	
	public function getMetaDescription(): string
	{
		return $this->metaDescription;
	}
	
	public function setMetaDescription(string $metaDescription): void
	{
		$this->metaDescription = $metaDescription;
	}
}