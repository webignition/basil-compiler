<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

interface CompilableSourceInterface
{
    /**
     * @return string[]
     */
    public function getStatements(): array;

    public function getClassDependencies(): ClassDependencyCollection;
    public function getVariableExports(): VariablePlaceholderCollection;
    public function getVariableDependencies(): VariablePlaceholderCollection;

    public function withClassDependencies(ClassDependencyCollection $classDependencies): CompilableSourceInterface;
    public function withVariableDependencies(
        VariablePlaceholderCollection $variableDependencies
    ): CompilableSourceInterface;
    public function withVariableExports(VariablePlaceholderCollection $variableExports): CompilableSourceInterface;

    public function __toString(): string;
}
