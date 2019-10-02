<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model\Call;

use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;

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

    public function extend(
        string $template,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ): CompilableSourceInterface {
        $extendedCompilableSource = $this->compilableSource->extend(
            $template,
            $useStatements,
            $variablePlaceholders
        );

        return new VariableAssignmentCall($extendedCompilableSource, $this->elementVariablePlaceholder);
    }

    public function getStatements(): array
    {
        return $this->compilableSource->getStatements();
    }

    public function withAdditionalStatements(array $statements): VariableAssignmentCall
    {
        $new = clone $this;
        $new->compilableSource = $this->compilableSource->withAdditionalStatements($statements);

        return $new;
    }

    public function getUseStatements(): UseStatementCollection
    {
        return $this->compilableSource->getUseStatements();
    }

    public function getVariablePlaceholders(): VariablePlaceholderCollection
    {
        return $this->compilableSource->getVariablePlaceholders();
    }

    public function __toString(): string
    {
        return $this->compilableSource->__toString();
    }
}
