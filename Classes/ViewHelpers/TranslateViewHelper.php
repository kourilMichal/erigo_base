<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use Erigo\ErigoBase\Utility\TypoScriptUtility;

class TranslateViewHelper extends AbstractViewHelper
{
	use CompileWithRenderStatic;
	
	protected $escapeChildren = false;
	
	/**
	 * @see \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper::initializeArguments()
	 */
	public function initializeArguments()
	{
        $this->registerArgument('key', 'string', 'Translation Key');
        
        $this->registerArgument(
            'default', 
            'string', 
            'If the given locallang key could not be found, this value is used. '.
                'If this argument is not set, child nodes will be used to render the default',
        );
        
        $this->registerArgument('arguments', 'array', 'Arguments to be replaced in the resulting string');
        
        $this->registerArgument(
            'languageKey', 
            'string', 
            'Language key ("da" for example) or "default" to use. If empty, use current language. '.
                'Ignored in non-extbase context.',
        );
	}
	
	/**
	 * @see \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper::renderStatic()
	 */
	public static function renderStatic(
	    array $arguments,
	    \Closure $renderChildrenClosure,
	    RenderingContextInterface $renderingContext,
    ): string
	{
		$key = $arguments['key'];
		$default = (string) ($arguments['default'] ?? $renderChildrenClosure() ?? '');
		$translateArguments = $arguments['arguments'];
		
	    $defaultThemeName = TypoScriptUtility::THEME_DEFAULT;
	    $currentThemeName = TypoScriptUtility::getFrontendTheme();
		
		$result = LocalizationUtility::translate(
		    'LLL:fileadmin/themes/'. $currentThemeName .'/lang/locallang.xlf:'. $key, 
		    null, 
		    $translateArguments, 
		    $arguments['languageKey'],
	    );
		
		if ($result === null && $currentThemeName != $defaultThemeName) {
    		$result = LocalizationUtility::translate(
    		    'LLL:fileadmin/themes/'. $defaultThemeName .'/lang/locallang.xlf:'. $key, 
    		    null, 
    		    $translateArguments, 
    		    $arguments['languageKey'], 
    	    );
		}
		
		if ($result !== null) {
		    return $result;
		}
		
	    if (!empty($translateArguments)) {
	        $default = vsprintf($default, $translateArguments);
	    }
		
		return $default;
	}
}