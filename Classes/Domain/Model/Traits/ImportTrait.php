<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

trait ImportTrait
{
	protected string $importId = '';
	protected string $importSource = '';
	
	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\ImportInterface::getImportId()
	 */
	public function getImportId(): string
	{
		return $this->importId;
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\ImportInterface::setImportId()
	 */
	public function setImportId(string $importId): void
	{
		$this->importId = $importId;
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\ImportInterface::getImportSource()
	 */
	public function getImportSource(): string
	{
		return $this->importSource;
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\ImportInterface::setImportSource()
	 */
	public function setImportSource(string $importSource): void
	{
		$this->importSource = $importSource;
	}
}