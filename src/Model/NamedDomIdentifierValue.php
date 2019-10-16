<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModel\Value\ValueInterface;

class NamedDomIdentifierValue implements ValueInterface
{
    private $domIdentifierValue;
    private $placeholder;

    public function __construct(DomIdentifierValue $domIdentifierValue, VariablePlaceholder $placeholder)
    {
        $this->domIdentifierValue = $domIdentifierValue;
        $this->placeholder = $placeholder;
    }

    public function getIdentifier(): DomIdentifierInterface
    {
        return $this->domIdentifierValue->getIdentifier();
    }

    public function getPlaceholder(): VariablePlaceholder
    {
        return $this->placeholder;
    }

    public function isEmpty(): bool
    {
        return $this->domIdentifierValue->isEmpty();
    }

    public function isActionable(): bool
    {
        return $this->domIdentifierValue->isActionable();
    }

    public function __toString(): string
    {
        return $this->getIdentifier()->__toString();
    }
}