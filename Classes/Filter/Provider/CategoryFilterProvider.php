<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Filter\Provider;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Erigo\ErigoBase\Domain\Repository\CategoryRepository;
use Erigo\ErigoBase\Filter\Tree\{TreeOption, TreeOptionCollection};

class CategoryFilterProvider extends AbstractTreeOptionsFilterProvider
{
    public function __construct(
        protected CategoryRepository $categoryRepository
    ) {}
    
	/**
	 * @see \Erigo\ErigoBase\Filter\Interfaces\TreeOptionsFilterProviderInterface::getAllTreeOptions()
	 */
	public function getAllTreeOptions(): TreeOptionCollection
	{
		$options = GeneralUtility::makeInstance(TreeOptionCollection::class);
		
		$query = $this->categoryRepository->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(false);
		$query->setOrderings([
				'parent' => QueryInterface::ORDER_ASCENDING,
				'sorting' => QueryInterface::ORDER_ASCENDING,
			]);
		
		foreach ($query->execute() as $category) {
			$parentValue = null;
			if ($category->getParent() instanceof Category) {
				$parentValue = $category->getParent()->getUid();
			}
			
			$options->add(GeneralUtility::makeInstance(
			        TreeOption::class, 
			        (string) $category->getUid(), 
    			    $category->getTitle(), 
    			    $parentValue,
			    ));
		}
		
		return $options;
	}
}