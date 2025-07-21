<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Tree;

use Erigo\ErigoBase\Collection\AbstractObjectCollection;

class TreeOptionCollection extends AbstractObjectCollection
{
	public function add(TreeOption $object): void
	{
		$this->objects[] = $object;
	}
}