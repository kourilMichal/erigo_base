<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Controller\Frontend;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Erigo\ErigoBase\Domain\Repository\{AbstractRepository, ContentFilterRepository};
use Erigo\ErigoBase\Filter\FilterManager;
use Erigo\ErigoBase\Filter\FilterValue;
use Erigo\ErigoBase\Filter\Interfaces\{DbValueOptionsFilterProviderInterface, OptionsFilterProviderInterface};
use Erigo\ErigoBase\Utility\UrlUtility;

abstract class AbstractFrontendFilterController extends AbstractFrontendController
{
	protected FilterManager $filterManager;
	protected ContentFilterRepository $filterRepository;
	
	public function injectFilterManager(FilterManager $filterManager): void
	{
	    $this->filterManager = $filterManager;
	}
	
	public function injectFilterRepository(ContentFilterRepository $filterRepository): void
	{
	    $this->filterRepository = $filterRepository;
	}
	
	/**
	 * Check if filters should be visible in frontend
	 */
	protected function areFiltersVisible(): bool
	{
		return (
			array_key_exists('filter', $this->settings) && is_array($this->settings['filter']) && 
			array_key_exists('show', $this->settings['filter']) && (bool) $this->settings['filter']['show']
		);
	}
	
	/**
	 * Get filter values with constraints - aktualizováno pro TYPO3 v13
	 */
	protected function getFilterValues(array $additionalConstraints = []): array
	{
		$filterValues = [];
		
		$constraints = $this->getConstraints($additionalConstraints);
		
		if ($this->areFiltersVisible() || count($constraints) > 0) {
			$this->view->assign('constraints', $constraints);
			
			$filterProviders = $this->getFilterProviders($constraints);
			
			foreach ($filterProviders as $filterProvider) {
				$slug = $filterProvider->getItem()->getSlug();
				$filterValue = $this->filterManager->getFilterValue($filterProvider->getItem()->getPlugin(), $slug);
				
				if ($filterValue !== null) {
					$filterValues[$slug] = GeneralUtility::makeInstance(
						FilterValue::class, 
					    $filterProvider, 
					    $filterValue,
					);
				}
			}
			
			$this->view->assign('filters', $filterProviders);
			$this->view->assign('filterValues', $filterValues);
			
			// Apply filter constraints to modify constraint values
			foreach ($constraints as $constraintField => $constraintValue) {
				$filterValues[$constraintField] = $constraintValue;
			}
			
		} else {
			$this->view->assign('filters', []);
		}

		return $filterValues;
	}
	
	/**
	 * Get filter providers - aktualizováno pro TYPO3 v13
	 */
	protected function getFilterProviders(array &$constraints): array
	{	
		$filterProviders = [];
		
		$arguments = $this->request->getQueryParams();
		$baseQuery = $this->getFilterBaseQuery($constraints);
			
		foreach ($this->filterRepository->findByContentObjectData($this->cObj->data) as $filterItem) {
			$slug = $filterItem->getSlug();
			
			try {
			    $filterProvider = $this->filterManager->getProvider($filterItem);
			    
			    if ($filterProvider instanceof DbValueOptionsFilterProviderInterface) {
				    $filterProvider->setBaseQuery(clone $baseQuery);
			    }
			    
			    if (array_key_exists($slug, $arguments)) {
				    $value = $filterProvider->prepareValue($arguments[$slug]);
				    $this->filterManager->setFilterValue($filterItem->getPlugin(), $slug, $value);
			    }
			    
			    $filterProviders[] = $filterProvider;
			    
			} catch (\Exception $e) {
			    // Log error but continue with other filters
			    $this->logException($e);
			}
		}

		$this->applyFilterConstraints($filterProviders, $constraints);
		
		return $filterProviders;
	}
	
	/**
	 * Apply filter constraints - aktualizováno pro TYPO3 v13
	 */
	protected function applyFilterConstraints(array &$filterProviders, array &$constraints): void
	{
		foreach ($constraints as $constraintField => $constraintValue) {
			foreach ($filterProviders as $filterProvider) {
				$filterItem = $filterProvider->getItem();
		
				if ($constraintField == $filterItem->getProperty()) {
					$constraintValues = $constraintValue;
						
					if (!is_array($constraintValues)) {
						$constraintValues = [$constraintValues];
					}
					
					$hasMoreOptionsThanConstraints = false;
					
					try {
					    $availableOptions = $filterProvider->getAvailableOptions();
					    $hasMoreOptionsThanConstraints = (count(array_diff(
						    array_keys($availableOptions), 
						    $constraintValues,
					    )) > 0);
					} catch (\Throwable $e) {
					    // Some filter providers might not support getAvailableOptions()
					    $hasMoreOptionsThanConstraints = false;
					}
					
					if ($filterProvider instanceof OptionsFilterProviderInterface) {
						foreach ($constraintValues as $constraintSingleValueKey => $constraintSingleValue) {
							if ($filterProvider->isValidOption($constraintSingleValue)) {
								$filterProvider->addConstraint($constraintSingleValue);
								unset($constraintValues[$constraintSingleValueKey]);
							}
						}
					} else {
						foreach ($constraintValues as $constraintSingleValueKey => $constraintSingleValue) {
							$filterProvider->addConstraint($constraintSingleValue);
							unset($constraintValues[$constraintSingleValueKey]);
						}
					}
						
					$filterConstraints = $filterProvider->getConstraints();
						
					if (count($filterConstraints) > 0) {
						$filterSlug = $filterItem->getSlug();
						$filterValue = $this->filterManager->getFilterValue($filterItem->getPlugin(), $filterSlug);
							
						if ($filterValue === null) {
							$filterValue = [];
						}
		
						if (!is_array($filterValue)) {
							$filterValue = [$filterValue];
						}
		
						if (count($filterValue) == 0 || $hasMoreOptionsThanConstraints) {
							foreach ($filterConstraints as $filterConstraint) {
								if (!in_array($filterConstraint, $filterValue)) {
									$filterValue[] = $filterConstraint;
								}
							}
						}
							
						if (count($filterValue) == 1) {
							$filterValue = current($filterValue);
						}
		
						$this->filterManager->setFilterValue($filterItem->getPlugin(), $filterSlug, $filterValue);
					}
						
					$constraintValue = $constraintValues;
				}
			}
				
			$constraints[$constraintField] = $constraintValue;
		}
		
		// Remove empty constraints
		$remainingConstraints = [];
		
		foreach ($constraints as $constraintField => $constraintValue) {
			if (
			    (is_array($constraintValue) && count($constraintValue) > 0) ||
				(!is_array($constraintValue) && $constraintValue !== null && $constraintValue !== '')
			) {
				$remainingConstraints[$constraintField] = $constraintValue;
			}
		}
		
		$constraints = $remainingConstraints;
	}
	
	/**
	 * Get constraints from settings - aktualizováno pro TYPO3 v13
	 */
	protected function getConstraints(array $additionalConstraints = []): array
	{
		$constraints = [];
		$nonFilterConstraints = $this->getNonFilterConstraints();
		
		if (
		    array_key_exists('constraints', $this->settings) && 
		    is_array($this->settings['constraints'])
	    ) {
			foreach ($this->settings['constraints'] as $field => $value) {
				if (in_array($field, $nonFilterConstraints)) {
					continue;
				}
				
				$fieldPrefix = $field;
		
				if (!is_array($value)) {
					$fieldPrefix = '';
					$value = [$field => $value];
				}
		
				$csvFields = $this->getCsvConstraintFields($fieldPrefix);
		
				foreach ($value as $prefixedField => $prefixedValue) {
					if (trim((string) $prefixedValue) == '') {
						continue;
					}
						
					if (in_array($prefixedField, $csvFields)) {
						$prefixedValue = GeneralUtility::trimExplode(',', (string) $prefixedValue, true);
		
						if (count($prefixedValue) < 1) {
							continue;
						}
					}
						
					$this->prepareConstraint($constraints, $prefixedField, $prefixedValue, $fieldPrefix);
				}
			}
		}
		
		return array_merge($constraints, $additionalConstraints);
	}

	/**
	 * Prepare individual constraint - můžete override v potomcích
	 */
	protected function prepareConstraint(array &$constraints, string $field, mixed $value, string $prefix = ''): void
	{
		$constraints[$field] = $value;
	}
	
	/**
	 * Get CSV constraint fields - můžete override v potomcích
	 */
	protected function getCsvConstraintFields(string $prefix = ''): array
	{
		return [];
	}
	
	/**
	 * Get non-filter constraints - můžete override v potomcích
	 */
	protected function getNonFilterConstraints(): array
	{
		return [];
	}
	
	/**
	 * Get base query for filtering - můžete override v potomcích
	 */
	protected function getFilterBaseQuery(array $constraints = []): ?QueryInterface
	{
		$repository = $this->getFilterBaseRepository();
		
		if ($repository instanceof AbstractRepository) {
			return $repository->getFilterQuery($constraints);
		}
		
		return null;
	}
	
	/**
	 * Get filter base repository - MUSÍ být implementováno v potomcích
	 */
	abstract protected function getFilterBaseRepository(): ?AbstractRepository;
	
	/**
	 * Redirect with filter parameters - aktualizováno pro TYPO3 v13
	 */
	protected function redirectFilter(array $filter = [], array $arguments = []): void
	{
		$filterValues = UrlUtility::getFilterValues($filter);

		$this->uriBuilder->reset()->setTargetPageUid(null)->setCreateAbsoluteUri(true);
			
		if (GeneralUtility::getIndpEnv('TYPO3_SSL')) {
			$this->uriBuilder->setAbsoluteUriScheme('https');
		}
		
		if (count($filterValues) > 0) {
			$this->uriBuilder->setArguments($filterValues);
			$this->redirectToUri($this->uriBuilder->uriFor('filter', $arguments));
			
		} elseif ($this->request->getMethod() == 'POST') {
			$this->redirectToUri($this->uriBuilder->uriFor('list', $arguments));
		}
	}
	
	/**
	 * Pomocná metoda pro logování chyb
	 */
	protected function logFilterError(\Throwable $exception, string $context = ''): void
	{
	    $message = 'Filter error';
	    if ($context) {
	        $message .= ' in ' . $context;
	    }
	    $message .= ': ' . $exception->getMessage();
	    
	    $this->logException($exception);
	}
	
	/**
	 * Kontrola validity filter provideru
	 */
	protected function isValidFilterProvider(mixed $filterProvider): bool
	{
	    return $filterProvider instanceof OptionsFilterProviderInterface ||
	           $filterProvider instanceof DbValueOptionsFilterProviderInterface;
	}
	
	/**
	 * Bezpečné získání filter hodnoty s fallback
	 */
	protected function getFilterValueSafely(string $plugin, string $slug, mixed $fallback = null): mixed
	{
	    try {
	        $value = $this->filterManager->getFilterValue($plugin, $slug);
	        return $value !== null ? $value : $fallback;
	    } catch (\Throwable $e) {
	        $this->logException($e);
	        return $fallback;
	    }
	}
}