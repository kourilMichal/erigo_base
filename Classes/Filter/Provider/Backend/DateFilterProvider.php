<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Provider\Backend;

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Erigo\ErigoBase\Filter\Provider\DateFilterProvider as DefaultDateFilterProvider;

class DateFilterProvider extends DefaultDateFilterProvider
{
	/**
	 * @see \Erigo\ErigoBase\Filter\Provider\DateFilterProvider::getRangeConstraint()
	 */
	public function getRangeConstraint(
	    QueryInterface $query, 
	    string $property, 
	    mixed $value,
    ): ConstraintInterface
	{
		if (array_key_exists('from', $value)) {
			$value['from'] = strtotime($value['from']);
		}

		if (array_key_exists('to', $value)) {
			$value['to'] = strtotime($value['to']);
		}
		
		return parent::getRangeConstraint($query, $property, $value);
	}
}