<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2022 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers\Backend;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

abstract class AbstractBackendViewHelper extends AbstractViewHelper
{
	/**
	 * @see \TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer::renderText()
	 */
	protected static function formatText(string $text): string
	{
	    $text = strip_tags($text);
	    $text = GeneralUtility::fixed_lgd_cs($text, 1500);
	    return nl2br(htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8', false));
	}
	
	/**
	 * @see \TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer::linkEditContent()
	 */
	protected static function linkEditContent(array $record, string $linkText): string
	{
	    $backendUser = static::getBackendUser();
	    
	    if (
	        $backendUser->check('tables_modify', 'tt_content') && 
	        $backendUser->recordEditAccessInternals('tt_content', $record)
        ) {
	        return '<a href="'. htmlspecialchars(static::getLinkUrl($record)) .'" title="'. 
        	   	        htmlspecialchars(static::getLanguageService()->sL(
        	   	            'LLL:EXT:typo3/sysext/backend/Resources/Private/Language/locallang_layout.xlf:edit',
    	   	            )) .'">'. $linkText .'</a>';
	    }
	    
	    return $linkText;
	}
	
	/**
	 * @see \TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer::linkEditContent()
	 */
	protected static function getLinkUrl(array $record): string
	{
	    $urlParameters = [
	        'edit' => [
	            'tt_content' => [
	                $record['uid'] => 'edit',
	            ],
	        ],
	        'returnUrl' => $GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams')->getRequestUri() . 
	                           '#element-tt_content-'. $record['uid'],
	    ];
	    
	    $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
	    
	    return (string) $uriBuilder->buildUriFromRoute('record_edit', $urlParameters);
	}
	
	protected static function getFlexFormConfig(string $flexFormText): array
	{
	    $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
	    
	    return $flexFormService->convertFlexFormContentToArray($flexFormText);
	}
	
	protected static function getBackendUser(): BackendUserAuthentication
	{
	    return $GLOBALS['BE_USER'];
	}
	
	protected static function getLanguageService(): LanguageService
	{
	    return $GLOBALS['LANG'];
	}
}