<?php 

defined('TYPO3') || die();

(function ($extKey) {
	
	// Registrace statického TypoScript souboru
	// V TYPO3 v13 tato funkcionalita zůstává stejná
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
		$extKey, 
		'Configuration/TypoScript/', 
		'ERIGO. Base'
	);
	
})(
	'erigo_base',
);