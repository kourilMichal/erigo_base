<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\View\StandaloneView;

class FluidUtility implements SingletonInterface
{
	public static function getStandaloneView(
	    string $templateName, 
	    array $rootPaths = [], 
	    array $variables = [],
    ): StandaloneView 
    {
		$standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
		$standaloneView->setTemplate($templateName);
		
		$pathTypes = ['template', 'partial', 'layout'];
		
		foreach ($pathTypes as $pathType) {
			$fullPathType = $pathType .'RootPaths';

			if (array_key_exists($fullPathType, $rootPaths)) {
				$method = 'set'. ucfirst($fullPathType);
				
				$standaloneView->$method($rootPaths[$fullPathType]);
			}
		}
		
		if (count($variables) > 0) {
			$standaloneView->assignMultiple($variables);
		}
		
		return $standaloneView;
	}
}