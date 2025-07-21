<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Erigo\ErigoBase\Domain\Model\AbstractEntity;
use Erigo\ErigoBase\Exception\NoReportException;

abstract class AbstractTransferTask extends AbstractTask
{
	protected PersistenceManager $persistenceManager;
	protected array $thrownExceptions = [];
	
	/**
	 * @see \TYPO3\CMS\Scheduler\Task\AbstractTask::execute()
	 */
	public function execute()
	{
		$this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
		
		$result = $this->executeTransfer();
		
		if (count($this->thrownExceptions) > 0) {
			$emailSubject = $this->translate(
				'task.transfer.error.email_subject',
				'ErigoBase',
				[
					$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
					$this->getTranslatedTaskTitle(),
				]
			);
			
		/*
			$this->sendExceptionsEmail($this->thrownExceptions, $emailSubject);
			
			throw new NoReportException($this->translate('task.transfer.error.message', 'ErigoBase'));
		*/
		}
		
		return $result;
	}
	
	abstract protected function executeTransfer(): bool;
	
	protected function addThrownException(\Exception $exception, AbstractEntity $object = null): void
	{
	//	$this->thrownExceptions[] = $this->convertExceptionToDataArray($exception, $object);
	}
}