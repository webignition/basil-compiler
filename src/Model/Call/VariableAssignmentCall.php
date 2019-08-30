<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Model\Call;

use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\VariablePlaceholder;

class VariableAssignmentCall
{
    private $transpilationResult;
    private $variablePlaceholder;

    public function __construct(
        TranspilationResultInterface $transpilationResult,
        VariablePlaceholder $variablePlaceholder
    ) {
        $this->transpilationResult = $transpilationResult;
        $this->variablePlaceholder = $variablePlaceholder;
    }

    public function getTranspilationResult(): TranspilationResultInterface
    {
        return $this->transpilationResult;
    }

    public function getVariablePlaceholder(): VariablePlaceholder
    {
        return $this->variablePlaceholder;
    }
}
