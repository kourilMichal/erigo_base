<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Form\Antispam;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Erigo\ErigoBase\Utility\TypoScriptUtility;

class AntispamSolutionManager implements SingletonInterface
{
    protected ?AntispamSolutionInterface $foundSolution = null;
    
	public function getSolution(): ?AntispamSolutionInterface
	{
	    if ($this->foundSolution === null) {
    		foreach ($this->findSolutions() as $solution) {
    		    if ($solution->isValid()) {
    		        $this->foundSolution = $solution;
    		        break;
    		    }
    		}
	    }
		
		return $this->foundSolution;
	}
	
	protected function findSolutions(): array
	{
	    $solutions = [];
	    $tsSolutions = TypoScriptUtility::getFrontendSettings('lib.antispam.solutions');
		
		if (is_array($tsSolutions)) {
		    krsort($tsSolutions);
		    
		    foreach ($tsSolutions as $solutionClass) {
		        $options = [];
		        
		        if (is_array($solutionClass)) {
		            if (!array_key_exists('className', $solutionClass)) {
		                continue;
		            }
		            
		            $solutionConfig = $solutionClass;
		            $solutionClass = $solutionConfig['className'];
		            unset($solutionConfig['className']);
		            
		            if (
		                array_key_exists('options', $solutionConfig) && 
		                is_array($solutionConfig['options'])
	                ) {
		                $options = $solutionConfig['options'];
		            }
		        }
		        
		        $solution = GeneralUtility::makeInstance($solutionClass);
		        
		        if ($solution instanceof AntispamSolutionInterface) {
		            $solution->setOptions($options);
		            
		            $solutions[] = $solution;
		        }
		    }
		}
		
		return $solutions;
	}
}