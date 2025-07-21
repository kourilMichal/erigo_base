<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2023 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Xclass\Scheduler\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\Domain\Repository as SchedulerRepository;
use TYPO3\CMS\Scheduler\Task as SchedulerTask;
use Erigo\ErigoBase\Task\AbstractTask;
use Erigo\ErigoBase\Utility\{EmailUtility, TypoScriptUtility};

class SchedulerTaskRepository extends SchedulerRepository\SchedulerTaskRepository
{
    /**
     * @see \TYPO3\CMS\Scheduler\Domain\Repository\SchedulerTaskRepository::addExecutionToTask()
     */
    public function addExecutionToTask(SchedulerTask\AbstractTask $task): int
    {
        if ($task instanceof AbstractTask) {
    		$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    		$queryBuilder = $connectionPool->getQueryBuilderForTable('tx_scheduler_task');
    		
    		$queryBuilder->select('lastexecution_time')
    			->from('tx_scheduler_task')
    			->where(
    				$queryBuilder->expr()->eq(
    				    'uid', 
    				    $queryBuilder->createNamedParameter($task->getTaskUid(), \PDO::PARAM_INT),
    			    ),
    				$queryBuilder->expr()->eq(
    				    'deleted', 
    				    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT),
    			    ),
    			)
    			->setMaxResults(1);
    		
    		$row = $queryBuilder->execute()->fetch();
    		if (!empty($row)) {
    			$task->setLastExecutionTime($row['lastexecution_time']);
    		}
        }
		
		return parent::addExecutionToTask($task);
    }
    
    /**
     * @see \TYPO3\CMS\Scheduler\Domain\Repository\SchedulerTaskRepository::removeExecutionOfTask()
     */
    public function removeExecutionOfTask(
        SchedulerTask\AbstractTask $task, 
        int $executionID, 
        array|string $failureReason = null
    ): void
    {
		parent::removeExecutionOfTask($task, $executionID, $failureReason);
		
		if ($failureReason !== null) {
			$this->preserveLastExecutionTime($task);

			if (PHP_SAPI === 'cli') {
			    $emailSubject = LocalizationUtility::translate(
			        'task.error.email_subject', 
			        'ErigoBase', 
			        [
						$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
						$task->getTranslatedTaskTitle(),
					], 
			        $this->getLanguageKey(),
		        );
				
				if (is_string($failureReason)) {
				    $failureReason = unserialize($failureReason);
				}
			
				if (is_array($failureReason)) {
    				$this->sendExceptionsEmail(
    					[$this->prepareFailureReasonArray($failureReason, $task)],
    					$emailSubject,
    					true,
    				);
				}
			}
		}
    }
	
	protected function preserveLastExecutionTime(SchedulerTask\AbstractTask $task): void
	{
	    if ($task instanceof AbstractTask) {
    		GeneralUtility::makeInstance(ConnectionPool::class)
    			->getConnectionForTable('tx_scheduler_task')
    			->update(
    				'tx_scheduler_task',
    			    [
    					'lastexecution_time' => $task->getLastExecutionTime(),
    				],
    				[
    					'uid' => $task->getTaskUid(),
    				],
    			);
	    }
	}
	
	protected function prepareFailureReasonArray(array $failureReason, SchedulerTask\AbstractTask $task): array
	{
	// message
		$messageParts = GeneralUtility::trimExplode('###', $failureReason['message'], true);
		$message = array_shift($messageParts);
		
	// params
		$params = [];
		
		foreach ($messageParts as $messagePart) {
			$paramParts = GeneralUtility::trimExplode(':', $messagePart, true, 2);
			
			$paramValue = '';
			if (count($paramParts) > 1) {
				$paramValue = $paramParts[1];
			}
			
			$params[$paramParts[0]] = $paramValue;
		}
		
		$failureReason['params'] = $params;
		
	// heading
		$failureReason['heading'] = $task->getTranslatedTaskTitle();
		
		return $failureReason;
	}
	
	protected function sendExceptionsEmail(array $exceptions, string $subject, bool $includeTrace = false): void
	{
		$recipients = ['servis@erigo.cz'];
		
		$emailOptions = [
			'from' => ['noreply@erigo.cz' => 'ERIGO NO-REPLY'],
			'subject' => $subject,
			'to' => $recipients,
		];

		EmailUtility::sendEmail(
			$emailOptions,
			'Exceptions', 
			EmailUtility::getEmailRootPaths($this->getExceptionsEmailRootPaths()),
			[
				'exceptions' => $exceptions,
				'languageKey' => $this->getLanguageKey(),
				'includeTrace' => $includeTrace,
			],
		);
	}
	
	protected function getExceptionsEmailRootPaths(): array
	{
		$theme = TypoScriptUtility::THEME_DEFAULT;
		
		$rootPaths = [
			'templateRootPaths' => [
				0 => 'EXT:erigo_base/Resources/Private/Templates/',
				999 => 'fileadmin/themes/'. $theme .'/view/Extensions/erigo_base/Templates/',
			],
			'partialRootPaths' => [
				0 => 'EXT:erigo_base/Resources/Private/Partials/',
				999 => 'fileadmin/themes/'. $theme .'/view/Extensions/erigo_base/Partials/',
			],
			'layoutRootPaths' => [
				0 => 'EXT:erigo_base/Resources/Private/Layouts/',
				999 => 'fileadmin/themes/'. $theme .'/view/Extensions/erigo_base/Layouts/',
			],
		];
		
		return $rootPaths;
	}
	
	protected function getLanguageKey(): string
	{
		/**
		 * @todo ...
		 */
		
		return 'cs';
	}
}