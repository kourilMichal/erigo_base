<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2022 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Service;

use TYPO3\CMS\Core\Resource as CoreResource;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model as ExtbaseModel;
use Erigo\ErigoBase\Domain\Model as BaseModel;
use Erigo\ErigoBase\Utility\TypoScriptUtility;

class ImageService implements ImageServiceInterface
{
    protected array $settings = [];
    protected array $viewConfiguration = [];
    
    public function __construct()
    {
        $libConfig = TypoScriptUtility::getFrontendSettings('lib.responsiveImages');
        
        $this->settings = $libConfig['settings'];
        $this->viewConfiguration = $libConfig['view'];
    }
	
    /**
     * @see \Erigo\ErigoBase\Service\ImageServiceInterface::getResponsiveVariants()
     */
	public function getResponsiveVariants(
	    array $contentData, 
	    array $files, 
	    string $widthType, 
	    array $pluginOptions,
    ): array 
    {
		if (count($this->settings) < 1) {
			return [];
		}
		
	// widths
	    $defaultWidths = $this->getDefaultWidths($widthType);
		$widths = $this->getWidths($contentData, $defaultWidths, $widthType);
		
	// breakpoints
		$breakpoints = $this->getBreakpoints();
		
	// item widths
		$itemWidths = $this->getItemWidths($contentData, $widthType);

	// media variants
		$variants = $this->getVariants($files, $widths, $breakpoints, $itemWidths);
		
	// sort variants
		$this->sortVariants($variants, $breakpoints, $widthType);
		
		return $variants;
	}
	
	protected function getWidths(array $contentData, array $defaultWidths, string $widthType): array
	{
	    return $defaultWidths;
	}
	
	protected function getDefaultWidths(string $widthType): array
	{
	    $defaultWidths = [];
		
		if (array_key_exists('containerWidths', $this->settings)) {
			foreach ($this->settings['containerWidths'] as $key => $widthData) {
			    if (array_key_exists($widthType, $widthData)) {
			        $defaultWidths[$key] = (int) $widthData[$widthType];
			    }
			}
		}
		
		return $defaultWidths;
	}
	
	protected function getItemWidths(array $contentData, string $widthType): array
	{
	    $itemWidths = [];
		
		if (array_key_exists('itemWidths', $this->settings)) {
			$itemWidths = $this->settings['itemWidths'];
		}
		
		return $itemWidths;
	}
	
	protected function getBreakpoints(): array
	{
	    $breakpoints = [];
		
		if (array_key_exists('breakpoints', $this->settings)) {
			$breakpoints = $this->settings['breakpoints'];
		}
		
		return $breakpoints;
	}
	
	protected function getVariants(array $files, array $widths, array $breakpoints, array $itemWidths): array
	{
		$variants = [];
		
		foreach ($files as $file) {
			if ($file instanceof BaseModel\FileReference) {
				$file = $file->getOriginalResource();
			}
			
			if ($file instanceof ExtbaseModel\FileReference) {
				$file = $file->getOriginalResource();
			}
			
			if ($file instanceof CoreResource\FileInterface) {
			    $filePublicUrl = $file->getPublicUrl();
			    $fileAbsUrl = GeneralUtility::getFileAbsFileName(ltrim($filePublicUrl, '/'));
			    
			    if (!is_file($fileAbsUrl)) {
			        continue;
			    }
			    
				$fileData = [
						'file' => $file,
				        'url' => $filePublicUrl,
						'variants' => [],
					];
				
			    $imageSize = getimagesize($fileAbsUrl);
			    
				if (is_array($imageSize)) {
			        $fileData['width'] = $imageSize[0];
			        $fileData['height'] = $imageSize[1];
				}
			        
					
				$cropData = false;
		
				if ($file instanceof CoreResource\FileReference) {
					try {
						$cropData = json_decode($file->getReferenceProperty('crop'), true);
		
					} catch (\Exception $e) {}
				}
		
				foreach ($widths as $key => $width) {
					$variantData = [
							'width' => $width,
							'breakpoint' => 0,
							'cropHash' => null,
						];
		
					foreach ($itemWidths as $itemWidth) {
						if (array_key_exists($key, $itemWidth)) {
							$variantData['width'] *= (float) $itemWidth[$key];
						}
					}
		
					if (array_key_exists($key, $breakpoints)) {
						$variantData['breakpoint'] = (int) $breakpoints[$key];
					}
		
					if (is_array($cropData) && array_key_exists($key, $cropData)) {
						try {
							$variantData['cropHash'] = md5(json_encode($cropData[$key]));
		
						} catch (\Exception $e) {}
					}
		
					$variantData['width'] = ceil($variantData['width']);
		
					$fileData['variants'][$key] = $variantData;
				}
		
				$variants[] = $fileData;
			}
		}
		
		return $variants;
	}
	
	protected function sortVariants(array &$variants, array $breakpoints, string $widthType): void
	{
		if ($widthType == ImageServiceInterface::WIDTH_TYPE_OUTER) {
		    $variantsOrder = array_reverse($breakpoints, true);
		    
			foreach ($variants as $i => $fileData) {
			    $sortedVariants = [];
			    
			    foreach ($variantsOrder as $breakpoint => $width) {
			        if (array_key_exists($breakpoint, $fileData['variants'])) {
			            $sortedVariants[$breakpoint] = $fileData['variants'][$breakpoint];
			        }
			    }
			
				$variants[$i]['variants'] = $sortedVariants;
			}
		}
	}
	
    /**
     * @see \Erigo\ErigoBase\Service\ImageServiceInterface::getViewConfiguration()
     */
	public function getViewConfiguration(): array
	{
	    return $this->viewConfiguration;
	}
	
    /**
     * @see \Erigo\ErigoBase\Service\ImageServiceInterface::isLazyLoadingEnabled()
     */
	public function isLazyLoadingEnabled(): bool
	{
	    if (array_key_exists('enableLazyLoading', $this->settings)) {
	        return (bool) $this->settings['enableLazyLoading'];
	    }
	    
		return false;
	}
}