<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Routing\Enhancer;

use TYPO3\CMS\Core\Routing\Route;
use TYPO3\CMS\Extbase\Routing\ExtbasePluginEnhancer;

class ApiEnhancer extends ExtbasePluginEnhancer
{
	const PAGE_TYPE = 3216001;
	const PATH_PREFIX = '/erigo-api';
	
	/**
	 * @see \TYPO3\CMS\Extbase\Routing\ExtbasePluginEnhancer::getVariant()
	 */
	protected function getVariant(Route $defaultPageRoute, array $configuration): Route
	{
		$route = parent::getVariant($defaultPageRoute, $configuration);
		
		$pathPrefix = self::PATH_PREFIX;
		if (array_key_exists('pathPrefix', $this->configuration)) {
			$pathPrefix .= $this->configuration['pathPrefix'];
		}
		
		$route->setPath($pathPrefix . $route->getPath());
		$route->setOption('_decoratedParameters', ['type' => self::PAGE_TYPE]);
		
		return $route;
	}
}