<?php

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ExtConfViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument(
		    'extensionKey', 
		    'string', 
		    'Extension key (lowercase_underscored format) to read configuration.', 
		    true,
	    );
		
		$this->registerArgument(
		    'path', 
		    'string', 
		    'Configuration path to read - if NULL, returns all configuration as array', 
		    false, 
		    '',
	    );
	}
	
	public function render(): mixed
	{
		return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(
		    $this->arguments['extensionKey'], 
		    $this->arguments['path'],
	    );
	}
}