<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers;

use TYPO3\CMS\Core\Utility\{ArrayUtility, GeneralUtility};
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RenderViewHelper extends AbstractViewHelper
{
	/** @var bool */
	protected $escapeOutput = false;
	
	public function initializeArguments(): void
	{
		$this->registerArgument('partial', 'string', 'Partial to render, with or without section.', true);
		$this->registerArgument('section', 'string', 'Section to render.');
		
		$this->registerArgument(
		    'arguments', 
		    'array', 
		    'Array of variables to be transferred. Use {_all} for all variables.',
		    false,
		    [],
	    );
		
		$this->registerArgument('settings', 'array', 'Setting which will be overrided.', false, []);
	}
	
	public function render(): string
	{
		$partial = $this->arguments['partial'];
		$section = $this->arguments['section'] ?? null;
		
		$variables = (array) $this->arguments['arguments'];
		$variables['settings'] = $this->renderingContext->getVariableProvider()->get('settings');
		
		if (is_array($this->arguments['settings'])) {
			ArrayUtility::mergeRecursiveWithOverrule(
			    $variables['settings'],
			    $this->arguments['settings'],
			    true,
			    true,
			    false,
		    );
		}
		
		$view = GeneralUtility::makeInstance(StandaloneView::class);
		
		$view->setTemplatePathAndFilename('EXT:erigo_base/Resources/Private/Partials/'. $partial .'.html');

		if (!empty($section)) {
			return $view->renderSection($section, $variables);
		}
		
		$view->assignMultiple($variables);
		
		return $view->render();
	}
}