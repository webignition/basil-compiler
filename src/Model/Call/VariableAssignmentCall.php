<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model\Call;

use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;

class VariableAssignmentCall implements CompilableSourceInterface
{
    private $compilableSource;
    private $elementVariablePlaceholder;

    public function __construct(
        CompilableSourceInterface $compilableSource,
        VariablePlaceholder $variablePlaceholder
    ) {
        $this->compilableSource = $compilableSource;
        $this->elementVariablePlaceholder = $variablePlaceholder;
    }

    public function getCompilableSource(): CompilableSourceInterface
    {
        return $this->compilableSource;
    }

    public function getElementVariablePlaceholder(): VariablePlaceholder
    {
        return $this->elementVariablePlaceholder;
    }

    public function getStatements(): array
    {
        return $this->compilableSource->getStatements();
    }

    public function withAdditionalStatements(array $statements): VariableAssignmentCall
    {
        $new = clone $this;
        $new->compilableSource = $this->compilableSource->withAdditionalStatements($statements);

        return $new;
    }

    public function getClassDependencies(): ClassDependencyCollection
    {
        return $this->compilableSource->getClassDependencies();
    }

    public function getVariableExports(): VariablePlaceholderCollection
    {
        return $this->compilableSource->getVariableExports();
    }

    public function getVariableDependencies(): VariablePlaceholderCollection
    {
        return $this->compilableSource->getVariableDependencies();
    }

    public function __toString(): string
    {
        return $this->compilableSource->__toString();
    }
}
