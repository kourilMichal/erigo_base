<?php

namespace Erigo\ErigoBase\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Param extends AbstractEntity
{
    protected string $title = '';
    protected string $slug = '';
    protected string $type = 'text';
    protected ?string $inputMode = null;
    protected ?string $defaultValue = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getInputMode(): ?string
    {
        return $this->inputMode;
    }

    public function setInputMode(?string $inputMode): void
    {
        $this->inputMode = $inputMode;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(?string $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }
}
