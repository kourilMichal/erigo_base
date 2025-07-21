<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Controller\Frontend;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\{GeneralUtility, PathUtility};
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;
use TYPO3Fluid\Fluid\View\ViewInterface;
use Erigo\ErigoBase\Controller\AbstractController;
use Erigo\ErigoBase\Domain\Model\Interfaces\GroupingInterface;
use Erigo\ErigoBase\Event\Controller\InitializeViewEvent;
use Erigo\ErigoBase\Service\MetadataService;
use Erigo\ErigoBase\Utility\TypoScriptUtility;

abstract class AbstractFrontendController extends AbstractController
{
	protected ContentObjectRenderer $cObj;
	protected MetadataService $metadataService;
	protected bool $isAjaxRequest = false;
	
	public function injectMetadataService(MetadataService $metadataService): void
	{
	    $this->metadataService = $metadataService;
	}
	
	/**
	 * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::initializeAction()
	 */
	protected function initializeAction(): void
	{
		parent::initializeAction();
		
		// V TYPO3 v13 použijeme request attributes místo $GLOBALS['TSFE']
		$this->cObj = $this->request->getAttribute('currentContentObject');
		$this->metadataService->setContentObject($this->cObj);
		
		$serverParams = $this->request->getServerParams();
		
		if (
		    array_key_exists('HTTP_X_REQUESTED_WITH', $serverParams) && 
		    strtolower($serverParams['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
	    ) {
	        $this->isAjaxRequest = true;
	    }
	}

	/**
	 * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::initializeView()
	 */
	protected function initializeView(ViewInterface $view): void
	{
		parent::initializeView($view);
		
		// POZOR: $GLOBALS['TSFE'] je deprecated v TYPO3 v13
		// Místo toho používáme request attributes
		$pageInformation = $this->request->getAttribute('frontend.page.information');
		
		if ($pageInformation !== null) {
			$view->assign('page', $pageInformation->getPageRecord());
		}
		
		$view->assignMultiple([
			'data' => $this->cObj->data,
			'isAjaxRequest' => $this->isAjaxRequest,
		]);
		
		// Event pro customizaci view
		$this->eventDispatcher->dispatch(
		    new InitializeViewEvent($this, $view, $this->request),
	    );
	}
	
	protected function getGroupingArray(iterable $collection, string $groupingProperty): array
	{
		$groups = [];
		
		foreach ($collection as $item) {
			if ($item instanceof GroupingInterface) {
				$item = $item->getGroupingEntity();
			}
			
			$groupingValue = ObjectAccess::getProperty($item, $groupingProperty);
			
			if (!array_key_exists($groupingValue, $groups)) {
				$groups[$groupingValue] = [];
			}
			
			$groups[$groupingValue][] = $item;
		}
		
		return $groups;
	}
	
	/**
	 * Aktualizováno pro TYPO3 v13 - místo $GLOBALS['TSFE'] používáme Site
	 */
	protected function getRootPageId(): int
	{
	    $site = $this->request->getAttribute('site');
	    
	    if ($site instanceof Site) {
	        return $site->getRootPageId();
	    }
	    
	    // Fallback pokud site není dostupný
	    return 1;
	}
	
	protected function translate(string $key, array $arguments = null): ?string
	{
		return LocalizationUtility::translate($key, $this->getRealExtensionName(), $arguments);
	}
	
	protected function translateLocally(string $key, array $arguments = null): ?string
	{
		$localFile = 'LLL:fileadmin/themes/'. TypoScriptUtility::getFrontendTheme() .'/lang/locallang.xlf';
		
		return LocalizationUtility::translate($localFile .':'. $key, null, $arguments);
	}
	
	protected function downloadFileResource(FileInterface $fileResource, bool $forceDownload = false): void
	{
	    $this->downloadFile(
           $fileResource->getPublicUrl(),
           [
               'name' => $fileResource->getNameWithoutExtension() .'.'. $fileResource->getExtension(),
               'type' => $fileResource->getMimeType(),
               'size' => $fileResource->getSize(),
           ],
           $forceDownload,
        );
	}
	
	protected function downloadFile(string $filePath, array $fileInfo = [], bool $forceDownload = false): void
	{
	    if (!PathUtility::isAbsolutePath($filePath)) {
	        $filePath = GeneralUtility::getFileAbsFileName($filePath);
	    }
	    
	    if (!is_file($filePath)) {
	        header('HTTP/1.0 404 Not Found');
	        exit;
	    }
	    
	    $fileInfo = array_replace(['name' => '', 'type' => '', 'size' => 0], $fileInfo);
	    
	    if ($fileInfo['name'] == '') {
	        $fileInfo['name'] = basename($filePath);
	    }
	    
	    if ($fileInfo['type'] == '') {
	        $fileInfo['type'] = (string) mime_content_type($filePath);
	    }
	        
        if ($fileInfo['type'] == '' || $forceDownload) {
            $fileInfo['type'] = 'application/octet-stream';
        }
	    
	    if ($fileInfo['size'] < 1) {
	        $fileInfo['size'] = filesize($filePath);
	    }
	    
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Description: File Transfer');
		header('Content-Type: '. $fileInfo['type']);
		header('Content-Disposition: attachment; filename="'. $fileInfo['name'] .'"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '. $fileInfo['size']);
		
		readfile($filePath);
		exit;
	}
	
	protected function getUriBuilder(): UriBuilder
	{
		$this->uriBuilder->reset()->setCreateAbsoluteUri(true);
		
		if (GeneralUtility::getIndpEnv('TYPO3_SSL')) {
			$this->uriBuilder->setAbsoluteUriScheme('https');
		}
		
		return $this->uriBuilder;
	}
	
	protected function redirectToPage(int $pageUid, array $arguments = []): ResponseInterface
	{
		$uriBuilder = $this->getUriBuilder()->setTargetPageUid($pageUid);
		
		if (count($arguments) > 0) {
			$uriBuilder->setArguments($arguments);
		}
		
		return $this->redirectToUri($uriBuilder->build());
	}
	
	// Ostatní metody zůstávají stejné...
}