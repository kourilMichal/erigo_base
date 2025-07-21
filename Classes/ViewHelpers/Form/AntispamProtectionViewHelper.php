<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2022 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers\Form;

use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use Erigo\ErigoBase\Form\Antispam\{AntispamSolutionInterface, AntispamSolutionManager};
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

class AntispamProtectionViewHelper extends AbstractFormFieldViewHelper
{
    protected $tagName = 'input';
    
    /**
     * @see \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper::__construct()
     */
    public function __construct(
        protected AntispamSolutionManager $antispamSolutionManager, 
        protected AssetCollector $assetCollector,
    ) {
        parent::__construct();
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        
        $this->registerUniversalTagAttributes();
    }
    
    /**
     * @see \TYPO3\CMS\Fluid\ViewHelpers\Form\HiddenViewHelper::render()
     */
    public function render()
    {
        $antispamSolution = $this->antispamSolutionManager->getSolution();
        
        if ($antispamSolution instanceof AntispamSolutionInterface) {
            $this->arguments['name'] = 'g-recaptcha-response';
            
            $antispamSolution->prepareProtectionField($this, $this->assetCollector);
            
            $this->tag->addAttribute('type', 'hidden');
            $this->tag->addAttribute('name', 'g-recaptcha-response');
            $this->tag->addAttribute('id', 'g-recaptcha-response');
            
            return $this->tag->render();
        }
        
        return '';
    }
    
    public function getTag(): TagBuilder
    {
        return $this->tag;
    }
    
    /**
     * @see \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper::getValueAttribute()
     */
    protected function getValueAttribute()
    {
        return '';
    }
}