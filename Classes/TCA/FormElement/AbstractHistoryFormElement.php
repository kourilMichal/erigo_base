<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\TCA\FormElement;

abstract class AbstractHistoryFormElement extends AbstractCustomFormElement
{
	/**
	 * @see \TYPO3\CMS\Backend\Form\AbstractNode::render()
	 */
	public function render()
	{
		$result = $this->initializeResultArray();
		
		$historyItems = $this->getHistoryItems();
		
		if (is_array($historyItems)) {
			$historyHtml = [];
			
			foreach ($historyItems as $historyItem) {
				$historyHtml[] = $this->wrapFieldOutput(implode(
				    ' &nbsp;&bull;&nbsp; ', 
				    $this->getHistoryItemData($historyItem),
			    ));
			}
			
			$result['html'] = $this->wrapOutput(implode(LF, $historyHtml));
			
		} else {
			$result['html'] = $this->wrapOutput('-');
		}
		
		return $result;
	}
	
	abstract protected function getFieldName(): string;
	
	protected function getHistoryItems(): ?array
	{
		$historyItems = $this->data['databaseRow'][$this->getFieldName()];
		
		if (!empty($historyItems)) {
			return json_decode($historyItems, true);
		}
		
		return null;
	}
	
	abstract protected function getHistoryItemData(array $historyItem): array;
}