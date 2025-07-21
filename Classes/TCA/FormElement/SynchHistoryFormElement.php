<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\TCA\FormElement;

use Erigo\ErigoBase\Domain\Model\Interfaces\SynchInterface;

class SynchHistoryFormElement extends AbstractHistoryFormElement
{
	/**
	 * @see \Erigo\ErigoBase\TCA\FormElement\AbstractHistoryFormElement::getFieldName()
	 */
	protected function getFieldName(): string
	{
		return 'synch_history';
	}

	/**
	 * @see \Erigo\ErigoBase\TCA\FormElement\AbstractHistoryFormElement::getHistoryItemData()
	 */
	protected function getHistoryItemData(array $historyItem): array
	{
		$historyItemData = [];
		$historyItemData[] = $this->formatTimestamp($historyItem['tstamp']);
		
		$typeHtml = $this->translate('tca.field.synch_history.type.'. $historyItem['type']);
		
		switch ($historyItem['type']) {
			case SynchInterface::TYPE_IMPORT:
				$typeHtml .= ' ('. $historyItem['source'] .')';
				break;

			case SynchInterface::TYPE_EXPORT:
				$typeHtml .= ' ('. $historyItem['target'] .')';
				break;
		}
		
		$historyItemData[] = $typeHtml;
		$historyItemData[] = $this->translate('tca.field.synch_history.action.'. $historyItem['action']);
		
		return $historyItemData;
	}
}