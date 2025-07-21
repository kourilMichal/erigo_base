<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Controller\Backend;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\{DeletedRestriction, WorkspaceRestriction};
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity as Extbase_AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\{
    Generic\Mapper, 
    ObjectStorage, 
    PersistenceManagerInterface, 
    QueryInterface
};
use Erigo\ErigoBase\Domain\Model\{AbstractEntity, TtContentFilter};
use Erigo\ErigoBase\Domain\Model\Interfaces\{HiddenInterface, ObjectNameInterface};
use Erigo\ErigoBase\Domain\Repository\AbstractRepository;
use Erigo\ErigoBase\Filter\FilterValue;
use Erigo\ErigoBase\Filter\Interfaces\{FilterProviderInterface};
use Erigo\ErigoBase\Filter\Provider\Backend\{
    DateFilterProvider, 
    NumberFilterProvider, 
    OptionsFilterProvider, 
    TextFilterProvider
};
use Erigo\ErigoBase\Utility\{IntlUtility, TcaUtility, UrlUtility};

abstract class AbstractBackendRepositoryController extends AbstractBackendController
{
	const FORMAT_TYPE_BOOLEAN = 'boolean';
	const FORMAT_TYPE_NUMBER = 'number';
	const FORMAT_TYPE_OBJECT = 'object';
	const FORMAT_TYPE_TCA = 'tca';
	
	protected PersistenceManagerInterface $persistenceManager;
	protected SiteFinder $siteFinder;
	protected AbstractRepository $repository;
	protected ?array $columnPropertyMap = null;
	protected int $listPid = 0;
	protected int $listLang = 0;
	protected ?string $listItemType = null;
	protected ?string $listSortBy = null;
	protected ?string $listSortDir = null;
	protected array $listFilter = [];
	protected int $listLimit = 20;
	protected int $listPage = 1;
	protected array $listPagination = ['enabled' => false];
	protected array $listColumns = [];
	protected ?array $listDisplayedColumns = null;
	protected ?array $listAvailableColumns = null;
	protected ?array $listAvailableLanguages = null;
	protected array $listLanguageIcons = [];
	protected array $listTcaItems = [];

	public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager): void
	{
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * @see \Erigo\ErigoBase\Controller\Backend\AbstractBackendController::initializeAction()
	 */
	protected function initializeAction(): void
	{
	    parent::initializeAction();
	    
		if ($this->getRequestParam('justLocalized')) {
			$this->redirectToEditJustLocalizedObject();
		}
		
		$this->siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
	}
	
	/**
	 * Initialize list action - aktualizováno pro TYPO3 v13
	 */
	protected function initializeListAction(): void
	{
	// storage
		$this->listPid = (int) $this->getUserModuleData('list', 'pid');
		
	// language
		$this->listLang = (int) $this->getUserModuleData('list', 'lang');
		
	// item type
		$this->listItemType = $this->getUserModuleData('list', 'itemType');
		
	// sorting
		$this->listSortBy = $this->getUserModuleData('list', 'sortBy');
		$this->listSortDir = $this->getUserModuleData('list', 'sortDir');
		
	// filter
		$this->listFilter = $this->getUserModuleData('list', 'filter') ?? [];
		
	// pagination
		$this->listLimit = (int) $this->getUserModuleData('list', 'limit') ?: 20;
		$this->listPage = (int) $this->getUserModuleData('list', 'page') ?: 1;
		
	// columns
		$this->listColumns = $this->getUserModuleData('list', 'columns') ?? [];
	}

	/**
	 * Main list action - aktualizováno pro TYPO3 v13
	 */
	public function listAction(): void
	{
		$this->initializeListAction();
		
		// Přidání menu a buttons
		$this->addListMenus();
		$this->addListNewRecordButton();
		
		// Získání dat pro zobrazení
		$rows = $this->getListRows();
		
		// Přiřazení dat do view
		$this->view->assignMultiple([
			'rows' => $rows,
			'columns' => $this->getListDisplayedColumns(),
			'availableColumns' => $this->getListAvailableColumns(),
			'pagination' => $this->listPagination,
			'filter' => $this->getListFilter(),
			'availableLanguages' => $this->getListAvailableLanguages(),
			'sorting' => $this->getListSorting(),
			'hasUserPageModifyPerms' => $this->hasUserListPageModifyPerms(),
		]);
		
		// Nastavení page title
		$this->moduleTemplate->setTitle($this->getPageTitle());
	}
	
	/**
	 * List actions - aktualizováno type hints pro TYPO3 v13
	 */
	public function listStorageAction(): void
	{
		$this->setUserModuleData('list', 'pid', $this->getRequestParam('pid'));
		$this->setUserModuleData('list', 'page', 1);
		
		$this->redirectToAction('list');
	}
	
	public function listLanguageAction(): void
	{
		$this->setUserModuleData('list', 'lang', $this->getRequestParam('lang'));
		$this->setUserModuleData('list', 'page', 1);
		
		$this->redirectToAction('list');
	}
	
	public function listItemTypeAction(): void
	{
		$this->setUserModuleData('list', 'itemType', $this->getRequestParam('itemType'));
		$this->setUserModuleData('list', 'page', 1);
		
		$this->redirectToAction('list');
	}
	
	public function listSortingAction(): void
	{
		$this->setUserModuleData('list', 'sortBy', $this->getRequestParam('sortBy'));
		$this->setUserModuleData('list', 'sortDir', $this->getRequestParam('sortDir'));
		$this->setUserModuleData('list', 'page', 1);
		
		$this->redirectToAction('list');
	}
	
	public function listFilterAction(): void
	{
		$currentFilter = $this->getUserModuleData('list', 'filter') ?? [];
		$newFilter = $this->getRequestParam('filter') ?? [];
		$page = (int) $this->getRequestParam('page') ?? 0;
		
		if ($newFilter != $currentFilter) {
			$page = 1;
		}
		
		$this->setUserModuleData('list', 'filter', $newFilter);
		
		if ($page > 0) {
			$this->setUserModuleData('list', 'page', $page);
		}
		
		$this->redirectToAction('list');
	}
	
	public function listPageAction(): void
	{
		$this->setUserModuleData('list', 'page', $this->getRequestParam('page'));
		
		$this->redirectToAction('list');
	}
	
	public function listLimitAction(): void
	{
		$this->setUserModuleData('list', 'limit', $this->getRequestParam('limit'));
		$this->setUserModuleData('list', 'page', 1);
		
		$this->redirectToAction('list');
	}
	
	public function listColumnAction(): void
	{
		$newColumns = $this->getRequestParam('columns') ?? [];
		
		$this->setUserModuleData('list', 'columns', array_keys($newColumns));
		
		$this->redirectToAction('list');
	}
	
	/**
	 * Property value retrieval - aktualizováno pro TYPO3 v13
	 */
	protected function getPropertyRawValue(AbstractEntity $object, string $property): mixed
	{
		return $object->{'get'. GeneralUtility::underscoredToUpperCamelCase($property)}();
	}
	
	protected function getPropertyValue(AbstractEntity $object, string $property): string
	{
		$value = $this->getPropertyRawValue($object, $property);
		
		if (is_object($value)) {
			return $this->formatObjectValue($value, $property);
		}
		
		if (is_array($value)) {
			return $this->formatArrayValue($value, $property);
		}
		
		if (is_bool($value)) {
			return $this->formatBooleanValue($value);
		}
		
		if (is_numeric($value)) {
			return $this->formatNumericValue($value, $property);
		}
		
		return (string) $value;
	}
	
	/**
	 * Value formatting methods - aktualizováno pro TYPO3 v13
	 */
	protected function formatObjectValue(object $value, string $property): string
	{
		if ($value instanceof \DateTime) {
			return IntlUtility::formatDate($value);
		}
		
		if ($value instanceof ObjectStorage) {
			$items = [];
			foreach ($value as $item) {
				$items[] = $this->getObjectName($item);
			}
			return implode(', ', $items);
		}
		
		return $this->getObjectName($value);
	}
	
	protected function formatArrayValue(array $value, string $property): string
	{
		$tcaSettings = $this->getListPropertyTcaSettings($property);
		
		if ($tcaSettings && $tcaSettings['config']['type'] === 'select') {
			$items = [];
			$tcaItems = $this->getListPropertyTcaItems($property);
			
			foreach ($value as $selectedValue) {
				foreach ($tcaItems as $tcaItem) {
				    // V TYPO3 v13 podporujeme různé formáty TCA items
				    $itemValue = is_array($tcaItem) && isset($tcaItem['value']) 
				        ? $tcaItem['value'] 
				        : ($tcaItem[1] ?? '');
				    $itemLabel = is_array($tcaItem) && isset($tcaItem['label']) 
				        ? $tcaItem['label'] 
				        : ($tcaItem[0] ?? '');
				        
					if ($itemValue == $selectedValue) {
						$items[] = $this->translateString($itemLabel);
						break;
					}
				}
			}
			
			return implode(', ', $items);
		}
		
		return implode(', ', $value);
	}
	
	protected function formatBooleanValue(bool $value): string
	{
		return $value ? 
		    $this->translate('labels.yes', 'core', 'core') : 
		    $this->translate('labels.no', 'core', 'core');
	}
	
	protected function formatNumericValue(mixed $value, string $property): string
	{
		$tcaSettings = $this->getListPropertyTcaSettings($property);
		
		if ($tcaSettings && isset($tcaSettings['config']['type'])) {
		    switch ($tcaSettings['config']['type']) {
		        case 'datetime':
		            if ($value > 0) {
		                return IntlUtility::formatDate(new \DateTime('@' . $value));
		            }
		            break;
		            
		        case 'number':
		            if (isset($tcaSettings['config']['format']) && $tcaSettings['config']['format'] === 'decimal') {
		                return number_format((float) $value, 2, ',', ' ');
		            }
		            break;
		    }
		}
		
		return (string) $value;
	}
	
	/**
	 * Icon methods - aktualizováno pro TYPO3 v13 IconSize enum
	 */
	protected function getListRowIcon(AbstractEntity $object): string
	{
		$tableName = $this->repository->getTableName();
		$recordArray = ['uid' => $object->getUid()];
		
		// V TYPO3 v13 používáme IconSize enum místo string konstant
		$icon = $this->iconFactory->getIconForRecord($tableName, $recordArray, IconSize::SMALL);
		
		return $icon->render();
	}
	
	protected function getSpaceIcon(): Icon
	{
		// V TYPO3 v13 používáme IconSize enum
		return $this->iconFactory->getIcon('empty-empty', IconSize::SMALL);
	}
	
	/**
	 * Button creation - aktualizováno pro TYPO3 v13
	 */
	protected function addListNewRecordButton(): void
	{
		if (!$this->hasUserListPageModifyPerms()) {
			return;
		}
		
		$buttonBar = $this->getDocHeader()->getButtonBar();
		$tableName = $this->repository->getTableName();
		$newLinkParams = '&edit['. $tableName .']['. $this->listPid .']=new';
		
		$newRecordButton = $buttonBar->makeLinkButton()
			->setHref('#')
			->setOnClick(htmlspecialchars(BackendUtility::editOnClick($newLinkParams)))
			->setTitle($this->translate('new', 'core', 'mod_web_list'))
			->setIcon($this->iconFactory->getIcon('actions-add', IconSize::SMALL));
		
		$buttonBar->addButton($newRecordButton, ButtonBar::BUTTON_POSITION_LEFT, 10);
	}
	
	/**
	 * Permission checks - bez změn pro v13
	 */
	protected function hasUserListPageModifyPerms(): bool
	{
		$hasPageModifyPerms = false;
		$pageRecord = BackendUtility::getRecord('pages', $this->listPid);
		
		if (is_array($pageRecord)) {
			$hasPageModifyPerms = $this->getUserAuth()->doesUserHaveAccess($pageRecord, 16);
		}
		
		return $hasPageModifyPerms;
	}
	
	/**
	 * Utility methods - aktualizováno type hints pro TYPO3 v13
	 */
	protected function getTcaEvalParts(array $tcaSettings): array
	{
		$evalParts = [];
		
		if (array_key_exists('eval', $tcaSettings['config'])) {
			$evalParts = GeneralUtility::trimExplode(',', $tcaSettings['config']['eval'], true);
		}
		
		return $evalParts;
	}
	
	protected function getTCA(): array
	{
		return $GLOBALS['TCA'][$this->repository->getTableName()];
	}
	
	protected function getAllowedPidSettingKey(): string
	{
		return 'backend/pid';
	}
	
	protected function getPageTitle(): ?string
	{
		$moduleConfiguration = $GLOBALS['TBE_MODULES']['_configuration'][$this->getModuleName()] ?? null;
		
		if ($moduleConfiguration && isset($moduleConfiguration['labels'])) {
		    return $this->translateString($moduleConfiguration['labels'] .':mlang_tabs_tab');
		}
		
		return null;
	}
	
	/**
	 * Localization handling - aktualizováno pro TYPO3 v13
	 */
	protected function redirectToEditJustLocalizedObject(): void
	{
		[$tableName, $parentObjectUid, $langUid] = GeneralUtility::trimExplode(
		    ':',
		    $this->getRequestParam('justLocalized'),
		    true
	    );
		
		$newObjectLocalization = $this->getTranslateTools()->translationInfo($tableName, $parentObjectUid, $langUid);
			
		if (
		    is_array($newObjectLocalization) && 
		    array_key_exists('translations', $newObjectLocalization) &&
			array_key_exists($langUid, $newObjectLocalization['translations'])
		) {
			$localizedObjectUid = $newObjectLocalization['translations'][$langUid]['uid'];
			
			$this->redirectToUri(
				$this->getUriBuilder()->buildUriFromRoute('record_edit')->__toString() 
					.'&edit['. $tableName .']['. $localizedObjectUid .']=edit&returnUrl='. 
					rawurlencode($this->getUriBuilder()->buildUriFromRoute($this->getModuleName())->__toString())
			);
		}
	}
	
	/**
	 * Object name handling - vylepšeno pro TYPO3 v13
	 */
	protected function getObjectName(object $object): string
	{
		if ($object instanceof ObjectNameInterface) {
			return $object->getObjectName();
		
		} else if ($object instanceof ObjectStorage) {
			$storageObjects = [];
			
			foreach ($object as $storageObject) {
				$storageObjects[] = $this->getObjectName($storageObject);
			}
			
			return implode(', ', $storageObjects);
		
		} else if ($object instanceof Extbase_AbstractEntity) {
			return (string) $object;
		
		} else if ($object instanceof \DateTime) {
			return IntlUtility::formatDate($object);
		} 
		
		return get_class($object);
	}
	
	/**
	 * @see \Erigo\ErigoBase\Controller\Backend\AbstractBackendController::getDefaultActionName()
	 */
	protected function getDefaultActionName(): string
	{
		return 'list';
	}
	
	/**
	 * Get standard actions for this controller type
	 */
	public static function getStandardActions(): array
	{
		return [
			'list', 
			'listStorage', 'listLanguage', 'listItemType', 
			'listSorting', 'listFilter', 'listPage', 'listLimit', 'listColumn',
		];
	}
}