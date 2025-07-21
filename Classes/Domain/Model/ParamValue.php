<?php

namespace Erigo\ErigoBase\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class ParamValue extends AbstractEntity
{
    protected ?int $param = null;
    protected string $foreignTable = '';
    protected int $foreignObject = 0;
    protected ?string $valueText = null;
    protected ?float $valueNumber = null;
    protected ?int $valueDate = null;
    protected ?bool $valueBoolean = null;

    public function getParam(): ?int
    {
        return $this->param;
    }

    public function setParam(?int $param): void
    {
        $this->param = $param;
    }

    public function getForeignTable(): string
    {
        return $this->foreignTable;
    }

    public function setForeignTable(string $foreignTable): void
    {
        $this->foreignTable = $foreignTable;
    }

    public function getForeignObject(): int
    {
        return $this->foreignObject;
    }

    public function setForeignObject(int $foreignObject): void
    {
        $this->foreignObject = $foreignObject;
    }

    public function getValueText(): ?string
    {
        return $this->valueText;
    }

    public function setValueText(?string $valueText): void
    {
        $this->valueText = $valueText;
    }

    public function getValueNumber(): ?float
    {
        return $this->valueNumber;
    }

    public function setValueNumber(?float $valueNumber): void
    {
        $this->valueNumber = $valueNumber;
    }

    public function getValueDate(): ?int
    {
        return $this->valueDate;
    }

    public function setValueDate(?int $valueDate): void
    {
        $this->valueDate = $valueDate;
    }

    public function getValueBoolean(): ?bool
    {
        return $this->valueBoolean;
    }

    public function setValueBoolean(?bool $valueBoolean): void
    {
        $this->valueBoolean = $valueBoolean;
    }
}
