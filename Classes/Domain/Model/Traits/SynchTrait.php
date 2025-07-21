<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity as Extbase_AbstractEntity;
use Erigo\ErigoBase\Domain\Model\Interfaces\ObjectNameInterface;

trait SynchTrait
{
	protected string $synchHistory = '';
	
	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\SynchInterface::getSynchHistory()
	 */
	public function getSynchHistory(): string
	{
		return $this->synchHistory;
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\SynchInterface::setSynchHistory()
	 */
	public function setSynchHistory(string $synchHistory): void
	{
		$this->synchHistory = $synchHistory;
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\SynchInterface::getSynchHistoryItems()
	 */
	public function getSynchHistoryItems(): array
	{
		$historyItems = [];
		
		try {
		    if (!empty($this->synchHistory)) {
		        $historyItems = json_decode($this->synchHistory, true, 20, JSON_THROW_ON_ERROR);
		    }
			
		} catch (\JsonException $e) {
			$messageParams = [get_class($this)];
			
			if ($this instanceof Extbase_AbstractEntity && !empty($this->getUid())) {
				$messageParams[] = 'UID:'. $this->getUid();
			}
			
			if ($this instanceof ObjectNameInterface) {
				$messageParams[] = '"'. $this->getObjectName() .'"';
			}
			
			throw new \JsonException($e->getMessage() .' ('. implode(', ', $messageParams) .')');
		}
		
		return $historyItems;
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\SynchInterface::addSynchHistoryItem()
	 */
	public function addSynchHistoryItem(array $synchHistoryItem, int $limit = 0): void
	{
		$synchHistoryItem['tstamp'] = $GLOBALS['EXEC_TIME'];
		
		$synchHistoryItems = $this->getSynchHistoryItems();
		array_unshift($synchHistoryItems, $synchHistoryItem);
		
		if ($limit < 1) {
			$limit = 100; // field of type TEXT in database is limited in size... 
		}
		
		if ($limit > 0) {
			$synchHistoryItems = array_slice($synchHistoryItems, 0, $limit);
		}
		
		$this->synchHistory = json_encode($synchHistoryItems);
	}
}