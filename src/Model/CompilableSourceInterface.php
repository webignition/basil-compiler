<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model;

interface CompilableSourceInterface
{
    /**
     * @return string[]
     */
    public function getStatements(): array;

    public function getCompilationMetadata(): CompilationMetadataInterface;
    public function withCompilationMetadata(
        CompilationMetadataInterface $compilationMetadata
    ): CompilableSourceInterface;

    public function __toString(): string;
}
