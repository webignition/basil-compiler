<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

interface TranspilationResultInterface
{
    public function extend(
        string $template,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ): TranspilationResultInterface;

    /**
     * @return string[]
     */
    public function getLines(): array;

    public function getUseStatements(): UseStatementCollection;
    public function getVariablePlaceholders(): VariablePlaceholderCollection;
    public function __toString(): string;
}
