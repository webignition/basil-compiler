<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class TranspilationResult
{
    private $content;
    private $useStatements;

    public function __construct(string $content)
    {
        $this->content = $content;
        $this->useStatements = new UseStatementCollection();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getUseStatements(): UseStatementCollection
    {
        return $this->useStatements;
    }

    public function withContent(string $content): TranspilationResult
    {
        $new = clone $this;
        $new->content = $content;

        return $new;
    }

    public function withAdditionalUseStatements(UseStatementCollection $useStatementCollection): TranspilationResult
    {
        $new = clone $this;
        $new->useStatements = $new->useStatements->withAdditionalUseStatements($useStatementCollection);

        return $new;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
