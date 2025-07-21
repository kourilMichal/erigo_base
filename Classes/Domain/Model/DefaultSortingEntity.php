<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model;

abstract class DefaultSortingEntity extends AbstractEntity implements Interfaces\HiddenInterface
{
	use Traits\SystemAttrsTrait;
	use Traits\HiddenTrait;
	use Traits\SortingTrait;
}