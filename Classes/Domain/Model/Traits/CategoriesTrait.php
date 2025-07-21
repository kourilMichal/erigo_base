<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

use TYPO3\CMS\Extbase\Annotation\ORM;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use Erigo\ErigoBase\Domain\Model\Category;

trait CategoriesTrait
{
	/** @var ObjectStorage<Category> */
    #[ORM\Lazy]
	protected $categories = null;

	public function getCategories(): ObjectStorage
	{
		return $this->categories;
	}
	
	public function setCategories(ObjectStorage $category): void
	{
		$this->categories = $category;
	}
	
	public function addCategory(Category $category): void
	{
		$this->categories->attach($category);
	}
	
	public function removeCategory(Category $category): void
	{
		$this->categories->detach($category);
	}
	
	public function hasCategory(Category $category): bool
	{
		return $this->categories->offsetExists($category);
	}

	public function getCategoriesGroupingData(): array
	{
		$groupingData = [];
				
		foreach ($this->getCategories() as $category) {
			$groupingData[] = [
					'key' => $category->getTitle() .'---'. $category->getUid(),
					'label' => $category->getTitle(),
					'value' => $category,
				];
		}
		
		return $groupingData;
	}
}