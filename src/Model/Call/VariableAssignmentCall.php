<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model\Call;

use webignition\BasilTranspiler\Model\TranspilableSourceInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;

class VariableAssignmentCall implements TranspilableSourceInterface
{
    private $transpilableSource;
    private $elementVariablePlaceholder;

    public function __construct(
        TranspilableSourceInterface $transpilableSource,
        VariablePlaceholder $variablePlaceholder
    ) {
        $this->transpilableSource = $transpilableSource;
        $this->elementVariablePlaceholder = $variablePlaceholder;
    }

    public function getTranspilationSource(): TranspilableSourceInterface
    {
        return $this->transpilableSource;
    }

    public function getElementVariablePlaceholder(): VariablePlaceholder
    {
        return $this->elementVariablePlaceholder;
    }

    public function extend(
        string $template,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ): TranspilableSourceInterface {
        $extendedTranspilableSource = $this->transpilableSource->extend(
            $template,
            $useStatements,
            $variablePlaceholders
        );

        return new VariableAssignmentCall($extendedTranspilableSource, $this->elementVariablePlaceholder);
    }

    public function getLines(): array
    {
        return $this->transpilableSource->getLines();
    }

    public function withAdditionalLines(array $lines): VariableAssignmentCall
    {
        $new = clone $this;
        $new->transpilableSource = $this->transpilableSource->withAdditionalLines($lines);

        return $new;
    }

    public function getUseStatements(): UseStatementCollection
    {
        return $this->transpilableSource->getUseStatements();
    }

    public function getVariablePlaceholders(): VariablePlaceholderCollection
    {
        return $this->transpilableSource->getVariablePlaceholders();
    }

    public function __toString(): string
    {
        return $this->transpilableSource->__toString();
    }
}
