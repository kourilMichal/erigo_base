<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

trait SystemAttrsTrait
{
	protected ?\DateTime $tstamp = null;
	protected ?\DateTime $crdate = null;
	
	public function getTstamp(): ?\DateTime
	{
		return $this->tstamp;
	}

	public function getCrdate(): ?\DateTime
	{
		return $this->crdate;
	}
}