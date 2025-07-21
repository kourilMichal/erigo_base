<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2023 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\ORM;

class Category extends StandardEntity implements Interfaces\ImportInterface
{
	use Traits\TitleSlugTrait;
	use Traits\ImagesTrait;
	use Traits\ImportTrait;
	use Traits\SynchTrait;
	
	/** @var Category */
    #[ORM\Lazy]
	protected $l10nParent = null;
	
// 	protected string $description = '';
	
	/** @var Category */
    #[ORM\Lazy]
	protected $parent = null;
	
// 	public function getDescription(): string
// 	{
// 	    return $this->description;
// 	}
	
// 	public function setDescription(string $description): void
// 	{
// 	    $this->description = $description;
// 	}
	
	public function getParent(): ?Category
	{
	    return $this->getLazyObject($this->parent);
	}
	
	public function setParent(?Category $parent): void
	{
	    $this->parent = $parent;
	}
}