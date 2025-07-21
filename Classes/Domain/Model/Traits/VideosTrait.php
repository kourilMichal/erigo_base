<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

use TYPO3\CMS\Extbase\Annotation\ORM;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use Erigo\ErigoBase\Domain\Model\FileReference;

trait VideosTrait
{
	/** @var ObjectStorage<FileReference> */
    #[ORM\Lazy]
    #[ORM\Cascade(['value' => 'remove'])]
	protected $videos = null;

	public function getVideos(): ObjectStorage
	{
		return $this->videos;
	}
	
	public function setVideos(ObjectStorage $videos): void
	{
		$this->videos = $videos;
	}
	
	public function addVideo(FileReference $video): void
	{
		$this->videos->attach($video);
	}
	
	public function removeVideo(FileReference $video): void
	{
		$this->videos->detach($video);
	}
}