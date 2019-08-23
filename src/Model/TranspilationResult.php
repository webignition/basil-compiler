<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class TranspilationResult
{
    private $content;
    private $useStatements;

    public function __construct(string $content, ?UseStatementCollection $useStatementCollection = null)
    {
        $this->content = $content;
        $this->useStatements = $useStatementCollection ?? new UseStatementCollection();
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

    public function __toString(): string
    {
        return $this->content;
    }
}
