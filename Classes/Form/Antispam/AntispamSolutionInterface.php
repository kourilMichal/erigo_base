<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Form\Antispam;

use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Error\Result;
use Erigo\ErigoBase\ViewHelpers\Form\AntispamProtectionViewHelper;

interface AntispamSolutionInterface extends SingletonInterface
{
    public function isValid(): bool;
    
    public function setOptions(array $options): void;
    
    public function prepareProtectionField(
        AntispamProtectionViewHelper $protectionField, 
        AssetCollector $assetCollector
    ): void;
	
	public function validateProtectionValue(string $protenctionFieldValue, string $protenctionFieldName): Result;
}