<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Service;

use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Resource as CoreResource;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Domain\Model as ExtbaseModel;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use Erigo\ErigoBase\PageTitle\PluginPageTitleProvider;
use Erigo\ErigoBase\Utility\TypoScriptUtility;

class MetadataService implements SingletonInterface
{
	protected ContentObjectRenderer $cObj;

	public function __construct(
	    protected MetaTagManagerRegistry $metaTagManagerRegistry,
    ) {}
	
	public function setContentObject(ContentObjectRenderer $cObj)
	{
	    $this->cObj = $cObj;
	}
	
	public function setPageMetadata(array $metadata): void
	{
		$availableMetadata = [
				'title', 'description', 'keywords',
				'og:title', 'og:description', 'og:image',
				'twitter:title', 'twitter:description', 'twitter:image',
			];
		
		foreach ($availableMetadata as $metadataName) {
			$metadataValue = null;
			
			if (array_key_exists($metadataName, $metadata)) {
				$metadataValue = $this->getMetadataValue($metadata[$metadataName]);
			}
			
			if ($metadataValue != '') {
				switch ($metadataName) {
					case 'title':
						PluginPageTitleProvider::setPageTitle($metadataValue);
						break;
					
					case 'description':
					case 'keywords':
					case 'og:title':
					case 'og:description':
					case 'og:image':
					case 'twitter:title':
					case 'twitter:description':
					case 'twitter:image':
						$this->setMetadata($metadataName, $metadataValue);
						break;
				}
				
			} else {
				switch ($metadataName) {
					case 'og:title':
				//	case 'twitter:title':
						if (array_key_exists('title', $metadata)) {
							$metadataValue = $this->getMetadataValue($metadata['title']);
						}
						break;

					case 'og:description':
				//	case 'twitter:description':
						if (array_key_exists('description', $metadata)) {
							$metadataValue = $this->getMetadataValue($metadata['description']);
						}
						break;

					case 'og:image':
				//	case 'twitter:image':
						if (array_key_exists('image', $metadata)) {
							$metadataValue = $this->getMetadataValue($metadata['image']);
						}
						break;
				}
				
				if ($metadataValue != '') {
					$this->setMetadata($metadataName, $metadataValue);
				}
			}
		}
	}
	
	protected function getMetadataValue(mixed $value): ?string
	{
		$metadataValue = null;
		
		if (!is_array($value)) {
			$value = [$value];
		}
		
		foreach ($value as $singleValue) {
			if (is_object($singleValue)) {
				$file = $singleValue;
				
				if ($file instanceof ExtbaseModel\FileReference) {
					$file = $file->getOriginalResource();
				}
				
				if ($file instanceof CoreResource\FileInterface) {
					$metadataValue = $this->cObj->typoLink('', [
							'parameter' => ltrim($file->getPublicUrl(), '/'),
							'forceAbsoluteUrl' => true,
							'returnLast' => 'url',
						]);
					break;
				}
				
			} else {
				if ($singleValue != '') {
					$metadataValue = $singleValue;
					break;
				}
			}
		}
		
		return $metadataValue;
	}
	
	protected function setMetadata(string $metadataName, string $metadataValue): void
	{
		$metaTagManager = $this->metaTagManagerRegistry->getManagerForProperty($metadataName);
		$metaTagManager->addProperty($metadataName, $metadataValue);
	}
}