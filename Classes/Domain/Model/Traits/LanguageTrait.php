<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

use TYPO3\CMS\Extbase\Annotation\ORM;
use Erigo\ErigoBase\Domain\Model\AbstractEntity;

trait LanguageTrait
{
	protected int $sysLanguageUid = 0;
	
	/** @var AbstractEntity */
    #[ORM\Lazy]
	protected $l10nParent = null;

	public function getSysLanguageUid(): int
	{
		return $this->sysLanguageUid;
	}
	
	public function setSysLanguageUid(int $sysLanguageUid): void
	{
		$this->sysLanguageUid = $sysLanguageUid;
		$this->_languageUid = $sysLanguageUid;
	}
	
	public function getL10nParent(): ?AbstractEntity
	{
		return $this->getLazyObject($this->l10nParent);
	}
	
	public function setL10nParent(AbstractEntity $l10nParent = null): void
	{
		$this->l10nParent = $l10nParent;
	}
}