<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Interfaces;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

interface DbValueOptionsFilterProviderInterface extends OptionsFilterProviderInterface
{
	public function setBaseQuery(?QueryInterface $baseQuery): void;
	
	public function getFieldName(): ?string;
}