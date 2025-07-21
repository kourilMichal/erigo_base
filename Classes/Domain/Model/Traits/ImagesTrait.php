<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

use TYPO3\CMS\Extbase\Annotation\ORM;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use Erigo\ErigoBase\Domain\Model\FileReference;

trait ImagesTrait
{
	/** @var ObjectStorage<FileReference> */
    #[ORM\Cascade(['value' => 'remove'])]
	protected $images = null;

	public function getImages(): ObjectStorage
	{
		return $this->images;
	}
	
	public function setImages(ObjectStorage $images): void
	{
		$this->images = $images;
	}

	public function addImage(FileReference $image): void
	{
		$this->images->attach($image);
	}

	public function removeImage(FileReference $image): void
	{
		$this->images->detach($image);
	}
	
	public function getFirstImage(): ?FileReference
	{
		return $this->getFirstObjectFromStorage($this->images);
	}
	
	public function getGalleryImages(): ObjectStorage
	{
		$galleryImages = clone $this->getImages();
		$firstImage = $this->getFirstImage();
		
		if ($firstImage instanceof FileReference) {
			$galleryImages->detach($firstImage);
		}
		
		return $galleryImages;
	}
}