<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

interface TranspilableSourceInterface
{
    public function extend(
        string $template,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ): TranspilableSourceInterface;

    /**
     * @return string[]
     */
    public function getStatements(): array;

    public function withAdditionalStatements(array $statements);
    public function getUseStatements(): UseStatementCollection;
    public function getVariablePlaceholders(): VariablePlaceholderCollection;
    public function __toString(): string;
}
