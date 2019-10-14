<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model\Call;

use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;

class VariableAssignmentCall implements CompilableSourceInterface
{
    private $compilableSource;
    private $variablePlaceholder;

    public function __construct(
        CompilableSourceInterface $compilableSource,
        VariablePlaceholder $variablePlaceholder
    ) {
        $this->compilableSource = $compilableSource;
        $this->variablePlaceholder = $variablePlaceholder;
    }

    public function getCompilableSource(): CompilableSourceInterface
    {
        return $this->compilableSource;
    }

    public function getVariablePlaceholder(): VariablePlaceholder
    {
        return $this->variablePlaceholder;
    }

    public function getStatements(): array
    {
        return $this->compilableSource->getStatements();
    }

    public function getCompilationMetadata(): CompilationMetadataInterface
    {
        return $this->compilableSource->getCompilationMetadata();
    }

    public function withCompilationMetadata(
        CompilationMetadataInterface $compilationMetadata
    ): CompilableSourceInterface {
        $new = clone $this;
        $new->compilableSource = $this->compilableSource->withCompilationMetadata($compilationMetadata);

        return $new;
    }

    public function mergeCompilationData(array $compilationDataCollection): CompilableSourceInterface
    {
        $new = clone $this;
        $new->compilableSource = $this->compilableSource->mergeCompilationData($compilationDataCollection);

        return $new;
    }

    public function __toString(): string
    {
        return $this->compilableSource->__toString();
    }
}
