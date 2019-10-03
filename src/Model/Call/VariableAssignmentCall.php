<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model\Call;

use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\CompilationMetadataInterface;
use webignition\BasilTranspiler\Model\VariablePlaceholder;

class VariableAssignmentCall implements CompilableSourceInterface
{
    private $compilableSource;
    private $elementVariablePlaceholder;

    public function __construct(
        CompilableSourceInterface $compilableSource,
        VariablePlaceholder $variablePlaceholder
    ) {
        $this->compilableSource = $compilableSource;
        $this->elementVariablePlaceholder = $variablePlaceholder;
    }

    public function getCompilableSource(): CompilableSourceInterface
    {
        return $this->compilableSource;
    }

    public function getElementVariablePlaceholder(): VariablePlaceholder
    {
        return $this->elementVariablePlaceholder;
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
