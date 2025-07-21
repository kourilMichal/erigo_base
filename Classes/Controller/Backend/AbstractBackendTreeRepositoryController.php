<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2022 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Controller\Backend;

use Erigo\ErigoBase\Domain\Model\AbstractEntity;

abstract class AbstractBackendTreeRepositoryController extends AbstractBackendRepositoryController
{
    protected array $flatObjects = [];
    protected array $flatRows = [];
    
    /**
     * @see \Erigo\ErigoBase\Controller\Backend\AbstractBackendRepositoryController::getListRows()
     */
	protected function getListRows(): array
	{
        $finalTree = [];
        
    // prepare parent-children relations
        $hasFilterValues = (count($this->listFilter) > 0);
        
        foreach (parent::getListRows() as $row) {
            if ($hasFilterValues) {
                unset($row['_actions']['secondGroup']['newAfter']);
                unset($row['_actions']['secondGroup']['moveUp']);
                unset($row['_actions']['secondGroup']['moveDown']);
            }
            
            $this->flatRows[$row['_uid']] = $row;
        }
        
        $parentChildrenRel = $this->createTreeRelations();
        
    // create tree
        if (array_key_exists(0, $parentChildrenRel)) {
            $finalTree = $this->recursiveCreateTree(0, 1, $parentChildrenRel);
        }
        
        return $finalTree;
	}
	
    /**
     * @see \Erigo\ErigoBase\Controller\Backend\AbstractBackendRepositoryController::getPropertyValue()
     */
	protected function getListRowMetadata(AbstractEntity $object): array
	{
	    $this->flatObjects[$object->getUid()] = $object;
	    
	    $metadata = parent::getListRowMetadata($object);
	    
	    $metadata['_inTree'] = false;
	    $metadata['_parent'] = 0;
	    $metadata['_sorting'] = $object->getSorting();
	    
	    $parentObject = $this->getTreeParentObject($object);
	    if ($parentObject instanceof AbstractEntity) {
	        $metadata['_parent'] = $parentObject->getUid();
	    }
	    
	    return $metadata;
	}
	    
    /**
     * @see \Erigo\ErigoBase\Controller\Backend\AbstractBackendRepositoryController::prepareListPagination()
     */
    protected function prepareListPagination(int $totalCount): void
    {
        $this->listPagination['totalCount'] = $totalCount;
        $this->listLimit = 1000;
    }
    
    protected function createTreeRelations(): array
    {
    // basic relations
        $parentChildrenRel = [];
        
        foreach ($this->flatRows as $objectUid => $row) {
            if (!array_key_exists($row['_parent'], $parentChildrenRel)) {
                $parentChildrenRel[$row['_parent']] = [];
            }
            
            $parentChildrenRel[$row['_parent']][] = $row['_uid'];
        }
        
    // find tree children from root
        if (array_key_exists(0, $parentChildrenRel)) {
            $this->recursiveFindTreeChildren(0, $parentChildrenRel);
        }
        
    // find tree parents to root
        $missingItems = [];
        foreach ($this->flatRows as $objectUid => $row) {
            if (!$row['_inTree']) {
                $missingItems[$objectUid] = $row;
            }
        }
        
        foreach ($missingItems as $objectUid => $row) {
            $this->recursiveFindTreeParents($objectUid, $parentChildrenRel);
        }
        
        return $parentChildrenRel;
    }
    
    protected function recursiveFindTreeChildren(int $parentUid, array $parentChildrenRel): void
    {
        if (array_key_exists($parentUid, $parentChildrenRel)) {
            foreach ($parentChildrenRel[$parentUid] as $childUid) {
                $this->recursiveFindTreeChildren($childUid, $parentChildrenRel);
                
                $this->flatRows[$childUid]['_inTree'] = true;
            }
        }
    }
    
    protected function recursiveFindTreeParents(
        int $objectUid,
        array &$parentChildrenRel, 
        bool $addRelation = false
    ): void
    {
        $row = $this->flatRows[$objectUid];
        
        if ($row['_parent'] > 0) {
            $parentObject = $this->getTreeParentObject($this->flatObjects[$objectUid]);
            
            if ($parentObject instanceof AbstractEntity) {
                if (!array_key_exists($row['_parent'], $this->flatRows)) {
                    $parentRow = $this->getListRowData($parentObject);
                    $parentRow['_class'] .= ' added-parent';
                    $parentRow['_inTree'] = true;
                    
                    $this->flatRows[$parentObject->getUid()] = $parentRow;
                }
                
                $this->recursiveFindTreeParents($parentObject->getUid(), $parentChildrenRel, true);
            }
        }
        
        if ($addRelation) {
            if (!array_key_exists($row['_parent'], $parentChildrenRel)) {
                $parentChildrenRel[$row['_parent']] = [];
            }
            
            $parentChildrenRel[$row['_parent']][] = $objectUid;
            
        } else {
            $this->flatRows[$objectUid]['_inTree'] = true;
        }
    }
    
    protected function recursiveCreateTree(int $parentUid, int $level, array $parentChildrenRel): array
    {
        $children = [];
        
        if (array_key_exists($parentUid, $parentChildrenRel)) {
            $sortedChildren = [];
            $maxPos = -1;
            foreach ($parentChildrenRel[$parentUid] as $childUid) {
                $sortedChildren[$childUid] = $this->flatRows[$childUid]['_sorting'];
                $maxPos++;
            }
            
            asort($sortedChildren);
            $sortedChildren = array_keys($sortedChildren);
            
            foreach ($sortedChildren as $pos => $childUid) {
                $this->flatRows[$childUid]['_class'] .= ' tree-level-'. $level;
                
                if (
                    $pos == 0 && 
                    array_key_exists('moveUp', $this->flatRows[$childUid]['_actions']['secondGroup'])
                ) {
                    $this->flatRows[$childUid]['_actions']['secondGroup']['moveUp'] = $this->getListEmptyAction();
                }
                
                if (
                    $pos == $maxPos && 
                    array_key_exists('moveDown', $this->flatRows[$childUid]['_actions']['secondGroup'])
                ) {
                    $this->flatRows[$childUid]['_actions']['secondGroup']['moveDown'] = $this->getListEmptyAction();
                }
                
                $children[] = [
                        'data' => $this->flatRows[$childUid],
                        'level' => $level,
                        'children' => $this->recursiveCreateTree($childUid, $level + 1, $parentChildrenRel),
                    ];
            }
        }
        
        return $children;
    }
    
    abstract protected function getTreeParentObject(AbstractEntity $object): ?AbstractEntity;
}