<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model\Call;

use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;

class VariableAssignmentCall implements TranspilationResultInterface
{
    private $transpilationResult;
    private $elementVariablePlaceholder;

    public function __construct(
        TranspilationResultInterface $transpilationResult,
        VariablePlaceholder $variablePlaceholder
    ) {
        $this->transpilationResult = $transpilationResult;
        $this->elementVariablePlaceholder = $variablePlaceholder;
    }

    public function getTranspilationResult(): TranspilationResultInterface
    {
        return $this->transpilationResult;
    }

    public function getElementVariablePlaceholder(): VariablePlaceholder
    {
        return $this->elementVariablePlaceholder;
    }

    public function extend(
        string $template,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ): TranspilationResultInterface {
        $extendedTranspilationResult = $this->transpilationResult->extend(
            $template,
            $useStatements,
            $variablePlaceholders
        );

        return new VariableAssignmentCall($extendedTranspilationResult, $this->elementVariablePlaceholder);
    }

    public function getLines(): array
    {
        return $this->transpilationResult->getLines();
    }

    public function getUseStatements(): UseStatementCollection
    {
        return $this->transpilationResult->getUseStatements();
    }

    public function getVariablePlaceholders(): VariablePlaceholderCollection
    {
        return $this->transpilationResult->getVariablePlaceholders();
    }

    public function __toString(): string
    {
        return $this->transpilationResult->__toString();
    }
}
