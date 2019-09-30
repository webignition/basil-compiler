<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class TranspilableSource implements TranspilableSourceInterface
{
    private $statements;
    private $useStatements;
    private $variablePlaceholders;

    public function __construct(
        array $statements,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ) {
        $this->statements = $statements;
        $this->useStatements = $useStatements;
        $this->variablePlaceholders = $variablePlaceholders;
    }

    public function extend(
        string $template,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ): TranspilableSourceInterface {
        return new TranspilableSource(
            explode("\n", sprintf($template, (string) $this)),
            $this->getUseStatements()->merge([$useStatements]),
            $this->getVariablePlaceholders()->merge([$variablePlaceholders])
        );
    }

    /**
     * @return string[]
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    public function getUseStatements(): UseStatementCollection
    {
        return $this->useStatements;
    }

    public function getVariablePlaceholders(): VariablePlaceholderCollection
    {
        return $this->variablePlaceholders;
    }

    public function withAdditionalStatements(array $statements): TranspilableSourceInterface
    {
        $new = clone $this;
        $new->statements = array_merge($this->statements, $statements);

        return $new;
    }

    public function __toString(): string
    {
        return implode("\n", $this->statements);
    }
}
