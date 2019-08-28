<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class TranspilationResult
{
    private $content;
    private $useStatements;
    private $variablePlaceholders;

    public function __construct(
        string $content,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ) {
        $this->content = $content;
        $this->useStatements = $useStatements;
        $this->variablePlaceholders = $variablePlaceholders;
    }

    public function extend(
        string $template,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ): TranspilationResult {
        return new TranspilationResult(
            sprintf($template, $this->getContent()),
            $this->getUseStatements()->merge([$useStatements]),
            $this->getVariablePlaceholders()->merge([$variablePlaceholders])
        );
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getUseStatements(): UseStatementCollection
    {
        return $this->useStatements;
    }

    public function getVariablePlaceholders(): VariablePlaceholderCollection
    {
        return $this->variablePlaceholders;
    }

    public function withContent(string $content): TranspilationResult
    {
        $new = clone $this;
        $new->content = $content;

        return $new;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
