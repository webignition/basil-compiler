<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;

class TranspilableSourceComposer
{
    public static function create(): TranspilableSourceComposer
    {
        return new TranspilableSourceComposer();
    }

    /**
     * @param string[] $statements
     * @param CompilableSourceInterface[] $calls
     * @param UseStatementCollection $useStatements
     * @param VariablePlaceholderCollection $variablePlaceholders
     *
     * @return CompilableSourceInterface
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

        return new CompilableSource($statements, $useStatements, $variablePlaceholders);
    }
}
