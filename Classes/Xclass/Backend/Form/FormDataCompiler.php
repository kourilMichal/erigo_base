<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Xclass\Backend\Form;

use TYPO3\CMS\Backend\Form as BackendForm;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Erigo\ErigoBase\TCA\ParamValue;

/**
 * FormDataCompiler Xclass pro TYPO3 v13
 * 
 * POZNÁMKA: V TYPO3 v13 se doporučuje používat PSR-14 events místo Xclass.
 * Tato třída by měla být migrována na event-based approach.
 * 
 * @deprecated V budoucích verzích bude nahrazena PSR-14 events
 */
class FormDataCompiler extends BackendForm\FormDataCompiler
{
    // V TYPO3 v13 je FormDataCompiler více extensible přes events
    // Doporučujeme migrovat na events jako:
    // - BeforeFormEnginePageInitializedEvent
    // - AfterFormEnginePageInitializedEvent
    // - ModifyEditFormUserAccessEvent
    
    // Prozatím ponecháváme prázdnou třídu pro kompatibilitu
}