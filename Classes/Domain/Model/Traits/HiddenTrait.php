<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

trait HiddenTrait
{
	protected bool $hidden = false;
	
	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\HiddenInterface::isHidden()
	 */
	public function isHidden(): bool
	{
		return $this->hidden;
	}
	
	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\HiddenInterface::getHidden()
	 */
	public function getHidden(): bool
	{
		return $this->isHidden();
	}
	
	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\HiddenInterface::setHidden()
	 */
	public function setHidden(bool $hidden): void
	{
		$this->hidden = $hidden;
	}
}