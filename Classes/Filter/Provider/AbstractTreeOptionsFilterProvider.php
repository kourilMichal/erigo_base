<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Provider;

use Erigo\ErigoBase\Filter\Interfaces\TreeOptionsFilterProviderInterface;
use Erigo\ErigoBase\Filter\Tree\{TreeOption, TreeOptionCollection};

abstract class AbstractTreeOptionsFilterProvider extends AbstractOptionsFilterProvider implements TreeOptionsFilterProviderInterface
{
	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\OptionsFilterProviderInterface::getAllOptions()
	 */
	public function getAllOptions(): array
	{
		return $this->makeOptionsTree($this->getAllTreeOptions());
	}

	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\AbstractOptionsFilterProvider::getAvailableOptions()
	 */
	public function getAvailableOptions(): array
	{
		return $this->makeOptionsTree($this->getAllTreeOptions(), false);
	}
	
	protected function makeOptionsTree(TreeOptionCollection $options, bool $useAllOptions = true): array
	{
		$treeOptions = [];
		$selectedOptions = [];
		$parentOption = null;
		
		if (!$useAllOptions) {
			switch ($this->getItemSettings('optionsMode')) {
				case self::OPTIONS_MODE_SELECTED:
					$selectedOptions = $this->getSelectedOptions();
					break;
					
				case self::OPTIONS_MODE_CHILDREN:
					$parentOption = $this->getParentOption();
					break;
			}
		}
		
		$useSelectedOptions = (count($selectedOptions) > 0);
		
		foreach ($options as $treeOption) {
			if ($useSelectedOptions && !in_array($treeOption->getValue(), $selectedOptions)) {
				continue;
			}
			
			if ($treeOption->getParentValue() != null ) {
				if (array_key_exists($treeOption->getParentValue(), $treeOptions)) {
					$treeOptions[$treeOption->getParentValue()]->addChild($treeOption);
				}
			}
			
			$treeOptions[$treeOption->getValue()] = $treeOption;
		}
		
		$sortedTreeOptions = [];
		$usedChildren = [];
		
		if ($parentOption !== null) {
			if (array_key_exists($parentOption, $treeOptions)) {
				$this->addChildrenOptionsToTree($treeOptions[$parentOption], $sortedTreeOptions, $usedChildren);
			}
			
		} else {
			foreach ($treeOptions as $treeOption) {
				if (in_array($treeOption->getValue(), $usedChildren)) {
					continue;
				}
				
				$sortedTreeOptions[$treeOption->getValue()] = $treeOption->getLabel();
				
				$this->addChildrenOptionsToTree($treeOption, $sortedTreeOptions, $usedChildren, 1);
			}
		}
		
		return $sortedTreeOptions;
	}
	
	protected function addChildrenOptionsToTree(
	    TreeOption $parentOption, 
	    array &$treeOptions, 
	    array &$usedChildren, 
		int $level = 0,
    ): void 
    {
		foreach ($parentOption->getChildren() as $childOption) {
			$treeOptions[$childOption->getValue()] = str_repeat('—', $level) .' '. $childOption->getLabel();
			$usedChildren[] = $childOption->getValue();
			
			$this->addChildrenOptionsToTree($childOption, $treeOptions, $usedChildren, $level + 1);
		}
	}
	
	protected function getParentOption(): string
	{
		return ($this->getItemSettings('parentOption') ?? '');
	}
}