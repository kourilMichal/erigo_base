<?php

defined('TYPO3') || die();

(function ($extKey) {

	$extPrefix = str_replace('_', '', $extKey);
	
	
	#############
	## Routing ##
	#############
	
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['PaginationMapper'] = 
	    \Erigo\ErigoBase\Routing\Aspect\PaginationMapper::class;
	
 	$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['FilterMapper'] = 
 	    \Erigo\ErigoBase\Routing\Aspect\FilterMapper::class;
 	
 	$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['MonthsMapper'] = 
 	    \Erigo\ErigoBase\Routing\Aspect\MonthsMapper::class;
 	
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers']['ErigoApi'] = 
	    \Erigo\ErigoBase\Routing\Enhancer\ApiEnhancer::class;
	
	
	####################
	## Class rewrites ##
	####################
	
	// POZOR: V TYPO3 v13 byly odstraněny některé třídy a změněna architektura
	// Následující řádky je potřeba zkontrolovat, jestli cílové třídy stále existují
	
	// extbase - zkontrolujte jestli tato třída stále existuje v v13
	if (class_exists(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class)) {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class] = [
			'className' => \Erigo\ErigoBase\Xclass\Extbase\Persistence\Storage\QueryParser::class,
		];
	}
	
	// scheduler - zkontrolujte jestli tato třída stále existuje v v13
	if (class_exists(\TYPO3\CMS\Scheduler\Domain\Repository\SchedulerRepository::class)) {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Scheduler\Domain\Repository\SchedulerRepository::class] = [
			'className' => \Erigo\ErigoBase\Xclass\Scheduler\Domain\Repository\SchedulerRepository::class,
		];
	}
	
	
	#################
	## Form engine ##
	#################
		
 	$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][$extKey .'__formElement__synchHistory'] = [
		'nodeName' => 'synchHistoryElement',
		'priority' => 90,
		'class' => \Erigo\ErigoBase\TCA\FormElement\SynchHistoryFormElement::class,
	];
	
	################
	## Stylesheet ##
	################
	
	$GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets'][$extKey] = 'EXT:'. $extKey .'/Resources/Public/Css/Backend/';
	
	// Antispam extension configuration
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['form']['antispam'] = [];
	
	// RTE nastavení - aktualizováno pro v13
	$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['default'] = 'EXT:erigo_base/Configuration/RTE/Default.yaml';
	
	// Poznámka: bodytext konfigurace by měla být v TCA override souboru místo zde
	

})(
	'erigo_base',
);