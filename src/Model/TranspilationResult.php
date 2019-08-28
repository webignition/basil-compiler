<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class TranspilationResult
{
    private $lines;
    private $useStatements;
    private $variablePlaceholders;

    public function __construct(
        array $lines,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ) {
        $this->lines = $lines;
        $this->useStatements = $useStatements;
        $this->variablePlaceholders = $variablePlaceholders;
    }

    public function extend(
        string $template,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ): TranspilationResult {
        return new TranspilationResult(
            explode("\n", sprintf($template, (string) $this)),
            $this->getUseStatements()->merge([$useStatements]),
            $this->getVariablePlaceholders()->merge([$variablePlaceholders])
        );
    }

    /**
     * @return string[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function getUseStatements(): UseStatementCollection
    {
        return $this->useStatements;
    }

    public function getVariablePlaceholders(): VariablePlaceholderCollection
    {
        return $this->variablePlaceholders;
    }

    public function __toString(): string
    {
        return implode("\n", $this->lines);
    }
}
