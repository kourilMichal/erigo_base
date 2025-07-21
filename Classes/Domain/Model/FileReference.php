<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model;

use TYPO3\CMS\Core\Resource\{ResourceFactory, ResourceInterface};
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM;

class FileReference extends StandardEntity
{
    use Traits\ForeignTableObjectTrait;
    
	/** @var FileReference */
    #[ORM\Lazy]
	protected $l10nParent = null;
	
	protected int $file;
	protected string $fieldName;
	protected int $sortingForeign = 0;
	protected string $localTable = 'sys_file';
	protected ?string $title = null;
	protected ?string $description = null;
	protected ?string $alternative = null;
	protected ?string $link = null;
	protected ?string $crop = null;
	
	/** @var ResourceInterface */
    #[ORM\Transient]
	protected $originalResource;
	
	/**
	 * @see \TYPO3\CMS\Extbase\Domain\Model\FileReference::getOriginalResource()
	 */
	public function getOriginalResource(): ResourceInterface
	{
		if ($this->originalResource === null) {
			$this->originalResource = GeneralUtility::makeInstance(ResourceFactory::class)
                            			->getFileReferenceObject($this->_localizedUid);
		}
		
		return $this->originalResource;
	}

	/**
	 * @see \TYPO3\CMS\Extbase\Domain\Model\FileReference::setOriginalResource()
	 */
	public function setOriginalResource(ResourceInterface $originalResource): void
	{
		$this->originalResource = $originalResource;
		$this->file = (int) $originalResource->getOriginalFile()->getUid();
	}
	

	public function getFile(): int
	{
		return $this->file;
	}
	
	public function setFile($file): void
	{
		$this->file = $file;
	}

	public function getFieldName(): string
	{
		return $this->fieldName;
	}
	
	public function setFieldName(string $fieldName): void
	{
		$this->fieldName = $fieldName;
	}
	
	public function getSortingForeign(): int
	{
		return $this->sortingForeign;
	}
	
	public function setSortingForeign($sortingForeign): void
	{
		$this->sortingForeign = $sortingForeign;
	}

	public function getLocalTable(): string
	{
		return $this->localTable;
	}
	
	public function setLocalTable(string $localTable): void
	{
		$this->localTable = $localTable;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}
	
	public function setTitle(string $title = null): void
	{
		$this->title = $title;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}
	
	public function setDescription(string $description = null): void
	{
		$this->description = $description;
	}

	public function getAlternative(): ?string
	{
		return $this->alternative;
	}
	
	public function setAlternative(string $alternative = null): void
	{
		$this->alternative = $alternative;
	}

	public function getLink(): ?string
	{
		return $this->link;
	}
	
	public function setLink(string $link = null): void
	{
		$this->link = $link;
	}

	public function getCrop(): ?string
	{
		return $this->crop;
	}
	
	public function setCrop(string $crop = null): void
	{
		$this->crop = $crop;
	}
}