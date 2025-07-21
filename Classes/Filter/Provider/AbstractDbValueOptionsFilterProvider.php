<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Provider;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Erigo\ErigoBase\Filter\Interfaces\DbValueOptionsFilterProviderInterface;

abstract class AbstractDbValueOptionsFilterProvider extends AbstractOptionsFilterProvider implements DbValueOptionsFilterProviderInterface
{
	protected ?QueryInterface $baseQuery = null;
	
	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\DbValueOptionsFilterProviderInterface::setBaseQuery()
	 */
	public function setBaseQuery(?QueryInterface $baseQuery): void
	{
		$this->baseQuery = $baseQuery;
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\DbValueOptionsFilterProviderInterface::getFieldName()
	 */
	public function getFieldName(): ?string
	{
		return null;
	}
}