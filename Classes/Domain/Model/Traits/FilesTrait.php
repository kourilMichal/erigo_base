<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

use TYPO3\CMS\Extbase\Annotation\ORM;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use Erigo\ErigoBase\Domain\Model\FileReference;

trait FilesTrait
{
	/** @var ObjectStorage<FileReference> */
    #[ORM\Lazy]
    #[ORM\Cascade(['value' => 'remove'])]
	protected $files = null;

	public function getFiles(): ObjectStorage
	{
		return $this->files;
	}
	
	public function setFiles(ObjectStorage $files): void
	{
		$this->files = $files;
	}

	public function addFile(FileReference $file): void
	{
		$this->files->attach($file);
	}

	public function removeFile(FileReference $file): void
	{
		$this->files->detach($file);
	}
	
	public function getFirstFile(): ?FileReference
	{
		return $this->getFirstObjectFromStorage($this->files);
	}
}