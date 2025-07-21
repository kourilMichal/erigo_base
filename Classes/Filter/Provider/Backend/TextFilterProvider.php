<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Provider\Backend;

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Erigo\ErigoBase\Domain\Repository\AbstractRepository;
use Erigo\ErigoBase\Filter\Provider\AbstractFilterProvider;

class TextFilterProvider extends AbstractFilterProvider
{
	/**
	 * @see \Erigo\ErigoBase\Filter\Provider\AbstractFilterProvider::applyValue()
	 */
	public function applyValue(
	    AbstractRepository $repository, 
	    QueryInterface $query, 
	    mixed $value,
    ): ConstraintInterface 
    {
		return $repository->applyFilterValue(
		    $query, 
		    $this->item->getQueryProperty(), 
		    $value, 
		    QueryInterface::OPERATOR_LIKE
	    );
	}
}