<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Identifier\DomIdentifierInterface;

class NamedDomIdentifier implements NamedDomIdentifierInterface
{
    private $identifier;
    private $placeholder;

    public function __construct(DomIdentifierInterface $identifier, VariablePlaceholder $placeholder)
    {
        $this->identifier = $identifier;
        $this->placeholder = $placeholder;
    }

    public function getIdentifier(): DomIdentifierInterface
    {
        return $this->identifier;
    }

    public function getPlaceholder(): VariablePlaceholder
    {
        return $this->placeholder;
    }

    public function includeValue(): bool
    {
        return null !== $this->identifier->getAttributeName();
    }
}
