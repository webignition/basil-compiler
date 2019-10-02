<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

interface CompilableSourceInterface
{
    public function extend(
        string $template,
        ClassDependencyCollection $classDependencies,
        VariablePlaceholderCollection $variablePlaceholders
    ): CompilableSourceInterface;

    /**
     * @return string[]
     */
    public function getStatements(): array;

    public function withAdditionalStatements(array $statements);
    public function getClassDependencies(): ClassDependencyCollection;
    public function getVariablePlaceholders(): VariablePlaceholderCollection;
    public function __toString(): string;
}
