<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Task;

use TYPO3\CMS\Extbase\Utility\{DebuggerUtility, LocalizationUtility};
use TYPO3\CMS\Scheduler\Task\AbstractTask as AbstractSchedulerTask;

abstract class AbstractTask extends AbstractSchedulerTask
{
	protected int $lastExecutionTime = 0;
	
    public function getLastExecutionTime(): int
    {
        return $this->lastExecutionTime;
    }

    public function setLastExecutionTime(int $lastExecutionTime): void
    {
        $this->lastExecutionTime = $lastExecutionTime;
    }
	
	protected function debug(mixed $variable, ?string $title = null, int $maxDepth = 20): void
	{
		DebuggerUtility::var_dump($variable, $title, $maxDepth);
	}
	
	protected function translate(string $key, ?string $extensionName = null, ?array $arguments = null): ?string
	{
		return LocalizationUtility::translate($key, $extensionName, $arguments, $this->getLanguageKey());
	}
	
	public function getTranslatedTaskTitle(): ?string
	{
		return $this->translate(
		    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][static::class]['title']
	    );
	}
}