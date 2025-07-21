<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Interfaces;

interface ImportInterface extends SynchInterface
{
	public function getImportId(): string;
	
	public function setImportId(string $importId): void;
	
	public function getImportSource(): string;
	
	public function setImportSource(string $importSource): void;
}