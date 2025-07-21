<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers\Plugin;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ClassesViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('settings', 'array', 'View settings.', true);
	}
	
	public function render(): string
	{
		$classes = [];
		$settings = $this->arguments['settings'];
		
		if (is_array($settings)) {
			foreach ($settings as $param => $value) {
				if ($value == '') {
					continue;
				}
				
				$classes[] = 'plugin-'. $param .'-'. $value;
			}
		}
		
		return implode(' ', $classes);
	}
}