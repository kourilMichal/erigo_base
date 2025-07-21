<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\ViewHelpers\Content;

class ResponsiveImagesViewHelper extends AbstractResponsiveImagesViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('files', 'array', 'Files or files references.', false, []);
		
		parent::initializeArguments();
	}

	protected function getFilesArray(): array
	{
		return $this->arguments['files'];
	}
	
	protected function getReturnResult(array $variants): array
	{
		return $variants;
	}
}