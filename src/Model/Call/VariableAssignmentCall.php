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

    public function withClassDependencies(ClassDependencyCollection $classDependencies): CompilableSourceInterface
    {
        return $this->compilableSource->withClassDependencies($classDependencies);
    }

    public function withVariableDependencies(
        VariablePlaceholderCollection $variableDependencies
    ): CompilableSourceInterface {
        return $this->compilableSource->withVariableDependencies($variableDependencies);
    }

    public function withVariableExports(VariablePlaceholderCollection $variableExports): CompilableSourceInterface
    {
        return $this->compilableSource->withVariableExports($variableExports);
    }
}
