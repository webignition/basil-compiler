<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;

class TranspilationResultComposer
{
    public static function create(): TranspilationResultComposer
    {
        return new TranspilationResultComposer();
    }

    /**
     * @param string[] $statements
     * @param TranspilationResult[] $calls
     * @param UseStatementCollection $useStatements
     * @param VariablePlaceholderCollection $variablePlaceholders
     *
     * @return TranspilationResult
     */
    public function compose(
        array $statements,
        array $calls,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ) {
        foreach ($calls as $call) {
            $useStatements = $useStatements->merge([$call->getUseStatements()]);
            $variablePlaceholders = $variablePlaceholders->merge([$call->getVariablePlaceholders()]);
        }

        return new TranspilationResult($statements, $useStatements, $variablePlaceholders);
    }
}
