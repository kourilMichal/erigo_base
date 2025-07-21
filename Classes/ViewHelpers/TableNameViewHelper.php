<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2022 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Erigo\ErigoBase\Utility\DomainUtility;

class TableNameViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('object', AbstractEntity::class, 'Object entity.', true);
	}
	
	public function render(): string
	{
		return DomainUtility::getTableNameFromClassName(get_class($this->arguments['object']));
	}
}