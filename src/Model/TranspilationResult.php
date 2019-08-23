<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class TranspilationResult
{
    private $content;

    /**
     * @var UseStatement[]
     */
    private $useStatements = [];

    public function __construct(string $content, array $useStatements = [])
    {
        $this->content = $content;

        foreach ($useStatements as $useStatement) {
            if ($useStatement instanceof UseStatement) {
                $this->useStatements[] = $useStatement;
            }
        }
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return UseStatement[]
     */
    public function getUseStatements(): array
    {
        return $this->useStatements;
    }

    public function withContent(string $content): TranspilationResult
    {
        $new = clone $this;
        $new->content = $content;

        return $new;
    }
}
