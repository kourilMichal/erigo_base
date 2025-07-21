<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Form\Antispam;

use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use Erigo\ErigoBase\ViewHelpers\Form\AntispamProtectionViewHelper;

abstract class AbstractAntispamSolution implements AntispamSolutionInterface
{
    protected array $options = [];
    
	/**
	 * @see \Erigo\ErigoBase\Form\Antispam\AntispamSolutionInterface::setOptions()
	 */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
    
	/**
	 * @see \Erigo\ErigoBase\Form\Antispam\AntispamSolutionInterface::prepareProtectionField()
	 */
    public function prepareProtectionField(
        AntispamProtectionViewHelper $protectionField, 
        AssetCollector $assetCollector
    ): void
    {}
    
    /**
	 * @see \Erigo\ErigoBase\Form\Antispam\AntispamSolutionInterface::getScript()
	 */
    public function getScript(string $formId): string
    {
        return '';
    }
}