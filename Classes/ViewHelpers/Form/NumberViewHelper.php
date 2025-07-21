<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers\Form;

use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

class NumberViewHelper extends AbstractFormFieldViewHelper
{
	/** @var string */
	protected $tagName = 'input';
	
	/**
	 * @see \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper::initializeArguments()
	 */
	public function initializeArguments(): void
	{
		parent::initializeArguments();
		
		$this->registerTagAttribute(
		    'autofocus',
		    'string',
		    'Specifies that an input should automatically get focus when the page loads',
	    );
		
		$this->registerTagAttribute(
		    'disabled',
		    'string',
		    'Specifies that the input element should be disabled when the page loads',
	    );
		
		$this->registerTagAttribute('readonly', 'string', 'The readonly attribute of the input field');
		$this->registerTagAttribute('placeholder', 'string', 'The placeholder of the textfield');
		$this->registerTagAttribute('min', 'float', 'The minimal possible value');
		$this->registerTagAttribute('max', 'float', 'The maximal possible value');
		$this->registerTagAttribute('step', 'float', 'The value step');
		
		$this->registerArgument(
		    'errorClass', 
		    'string', 
		    'CSS class to set if there are errors for this ViewHelper',
		    false,
		    'f3-form-error',
	    );

		$this->registerUniversalTagAttributes();
		
		$this->registerArgument('required', 'bool', 'If the field is required or not', false, false);
	}
	
	/**
	 * @see \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper::render()
	 */
	public function render()
	{
		$required = $this->arguments['required'] ?? false;
		
		$name = $this->getName();
		$this->registerFieldNameForFormTokenGeneration($name);
		$this->setRespectSubmittedDataValue(true);
		
		$this->tag->addAttribute('type', 'number');
		$this->tag->addAttribute('name', $name);
		
		$value = $this->getValueAttribute();
		
		if ($value !== null) {
			$this->tag->addAttribute('value', $value);
		}
		
		if ($required !== false) {
			$this->tag->addAttribute('required', 'required');
		}
		
		$this->addAdditionalIdentityPropertiesIfNeeded();
		$this->setErrorClassAttribute();
		
		return $this->tag->render();
	}
}