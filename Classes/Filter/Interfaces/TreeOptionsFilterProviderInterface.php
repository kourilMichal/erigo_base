<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Interfaces;

use Erigo\ErigoBase\Filter\Tree\TreeOptionCollection;

interface TreeOptionsFilterProviderInterface extends OptionsFilterProviderInterface
{
	const OPTIONS_MODE_CHILDREN = 'children';
	
	public function getAllTreeOptions(): TreeOptionCollection;
}