<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

interface CompilableSourceInterface
{
    public function extend(
        string $template,
        ClassDependencyCollection $classDependencies,
        VariablePlaceholderCollection $variableExports,
        VariablePlaceholderCollection $variableDependencies
    ): CompilableSourceInterface;

    /**
     * @return string[]
     */
    public function getStatements(): array;

    public function withAdditionalStatements(array $statements);
    public function getClassDependencies(): ClassDependencyCollection;
    public function getVariableExports(): VariablePlaceholderCollection;
    public function getVariableDependencies(): VariablePlaceholderCollection;
    public function __toString(): string;
}
