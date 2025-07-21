<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\PageTitle;

use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PluginPageTitleProvider extends AbstractPageTitleProvider
{
	public static function setPageTitle(string $title): void
	{
		$classInstance = GeneralUtility::makeInstance(self::class);
		$classInstance->setTitle($title);
	}
	
	public function setTitle(string $title): void
	{
		$this->title = $title;
	}
}