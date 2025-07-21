<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Interfaces;

interface HiddenInterface
{
    public function isHidden(): bool;
    
	public function getHidden(): bool;
	
	public function setHidden(bool $hidden): void;
}