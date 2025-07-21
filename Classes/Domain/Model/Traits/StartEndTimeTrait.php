<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

trait StartEndTimeTrait
{
	protected \DateTime $starttime = null;
	protected \DateTime $endtime = null;
	
	public function getStarttime(): ?\DateTime
	{
		return $this->starttime;
	}

	public function setStarttime(\DateTime $starttime = null): void
	{
		$this->starttime = $starttime;
	}

	public function getEndtime(): ?\DateTime
	{
		return $this->endtime;
	}

	public function setEndtime(\DateTime $endtime = null): void
	{
		$this->endtime = $endtime;
	}
}