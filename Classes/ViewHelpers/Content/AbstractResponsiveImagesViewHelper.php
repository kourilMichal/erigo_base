<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers\Content;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Erigo\ErigoBase\Service\ImageServiceInterface;
use Erigo\ErigoBase\Utility\FluidUtility;

abstract class AbstractResponsiveImagesViewHelper extends AbstractViewHelper
{
	protected $escapeOutput = false;
	
	public function __construct(
	    protected ImageServiceInterface $imageService,
    ) {}
	
	public function initializeArguments(): void
	{
		$this->registerArgument('data', 'array', 'Data of content element.', false, []);
		$this->registerArgument('onBackground', 'bool', 'Are images on background.', false, false);
		$this->registerArgument('return', 'bool', 'Do not render, but return variants array.', false, false);
		$this->registerArgument('pluginOptions', 'array', 'Action name (for use in plugins).', false, []);
		$this->registerArgument('variants', 'array', 'Already calculated variants.', false, []);
		
		$this->registerArgument(
		    'imageServiceClassName', 
		    'string', 
		    'Image service class name.', 
		    false, 
		    get_class($this->imageService),
	    );
	}

	public function render(): string|array
	{
	    // V TYPO3 v13 získáváme service přes dependency injection místo statického přístupu
	    $imageServiceClassName = $this->arguments['imageServiceClassName'];
	    $imageService = GeneralUtility::getContainer()->get($imageServiceClassName);
	    
		$variants = $this->arguments['variants'];
		
		if (count($variants) < 1) {
			$widthType = $this->arguments['onBackground'] ? 
			    ImageServiceInterface::WIDTH_TYPE_OUTER : 
			    ImageServiceInterface::WIDTH_TYPE_INNER;
			
			$pluginOptions = array_replace_recursive([
				'extension' => null,
				'action' => null,
			], $this->arguments['pluginOptions']);
			
			$variants = $imageService->getResponsiveVariants(
				$this->arguments['data'], 
			    $this->getFilesArray(), 
			    $widthType, 
			    $pluginOptions,
			);
		}
		
		if ($this->arguments['return']) {
			return $this->getReturnResult($variants);
		}
		
		return $this->renderHtml(
		    $variants, 
		    $imageService->getViewConfiguration(),
		    $imageService->isLazyLoadingEnabled(),
	    );
	}
	
	abstract protected function getFilesArray(): array;
	
	abstract protected function getReturnResult(array $variants): array;
	
	protected function renderHtml(
	    array $variants,
	    array $viewConfiguration,
	    bool $enableLazyLoading = true,
    ): string
	{
	    if (
	        !array_key_exists('templateName', $viewConfiguration) || 
	        empty($viewConfiguration['templateName'])
        ) {
	        throw new \InvalidArgumentException('Template name must be specified.');
	    }
	    
	    $templateName = $viewConfiguration['templateName'];
	    unset($viewConfiguration['templateName']);
	    
		$view = FluidUtility::getStandaloneView(
		    $templateName, 
		    $viewConfiguration, 
		    [
				'images' => $variants,
				'enableLazyLoading' => $enableLazyLoading,
		    ],
	    );
		
		$view->setFormat('html');
		
		return $view->render();
	}
}