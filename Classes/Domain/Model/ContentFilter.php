<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model;

use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Erigo\ErigoBase\Utility\FilterUtility;

class ContentFilter extends DefaultSortingEntity
{
	use Traits\TitleSlugTrait;
	
	protected int $contentUid = 0;
	protected string $plugin = '';
	protected string $property = '';
	protected string $settings = '';
	protected ?array $settingsArray = null;
	
	public function getContentUid(): int
	{
		return $this->contentUid;
	}
	
	public function setContentUid(int $contentUid): void
	{
		$this->contentUid = $contentUid;
	}
	
	public function getPlugin(): string
	{
		return $this->plugin;
	}
	
	public function setPlugin(string $plugin): void
	{
		$this->plugin = $plugin;
	}
	
	public function getProperty(): string
	{
		return $this->property;
	}
	
	public function setProperty(string $property): void
	{
		$this->property = $property;
	}
	
	public function getQueryProperty(): string
	{
		return str_replace('_', '.', $this->property);
	}
	
	public function getSettings(): string
	{
		return $this->settings;
	}
	
	public function setSettings(string $settings): void
	{
		$this->settings = $settings;
	}
	
	public function getSettingsArray(): array
	{
		if (!is_array($this->settingsArray)) {
			$this->settingsArray = [];

			if ($this->settings != '') {
				$flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
			
				$this->settingsArray = $flexFormService->convertFlexFormContentToArray($this->settings);
			}
		}
		
		return $this->settingsArray;
	}
	
	public function setSettingsArray(array $settingsArray): void
	{
		$this->settingsArray = $settingsArray;
	}
}