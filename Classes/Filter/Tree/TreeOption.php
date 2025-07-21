<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Tree;

class TreeOption
{
	protected array $children = [];
	
	public function __construct(
	    protected string $value = '', 
	    protected string $label = '', 
	    protected ?string $parentValue = null,
    ) {}

	public function getValue(): string
	{
		return $this->value;
	}
	
	public function setValue(string $value): void
	{
		$this->value = $value;
	}
	
	public function getLabel(): string
	{
		return $this->label;
	}
	
	public function setLabel(string $label): void
	{
		$this->label = $label;
	}

	public function getParentValue(): ?string
	{
		return $this->parentValue;
	}
	
	public function setParentValue(?string $parentValue): void
	{
		$this->parentValue = $parentValue;
	}
	
	public function addChild(TreeOption $child): void
	{
		$this->children[] = $child;
	}
	
	public function getChildren(): array
	{
		return $this->children;
	}
}