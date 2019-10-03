<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

class CompilableSource implements CompilableSourceInterface
{
    /**
     * @var string[]
     */
    private $statements;

    private $compilationMetadata;

    public function __construct(array $statements)
    {
        $this->statements = $statements;

        $this->compilationMetadata = new CompilationMetadata();
    }

    /**
     * @return string[]
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    public function getCompilationMetadata(): CompilationMetadataInterface
    {
        return $this->compilationMetadata;
    }

    public function withCompilationMetadata(
        CompilationMetadataInterface $compilationMetadata
    ): CompilableSourceInterface {
        $new = clone $this;
        $new->compilationMetadata = $compilationMetadata;

        return $new;
    }

    public function __toString(): string
    {
        return implode("\n", $this->statements);
    }
}
