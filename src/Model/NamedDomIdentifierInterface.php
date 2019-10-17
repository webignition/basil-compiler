<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Identifier\DomIdentifierInterface;

interface NamedDomIdentifierInterface
{
    public function getIdentifier(): DomIdentifierInterface;
    public function getPlaceholder(): VariablePlaceholder;
    public function includeValue(): bool;
    public function asCollection(): bool;
}
