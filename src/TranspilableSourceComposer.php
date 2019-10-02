<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
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
     * @param ClassDependencyCollection $classDependencies
     * @param VariablePlaceholderCollection $variableExports
     * @param VariablePlaceholderCollection $variableDependencies
     *
     * @return CompilableSourceInterface
     */
    public function compose(
        array $statements,
        array $calls,
        ClassDependencyCollection $classDependencies,
        VariablePlaceholderCollection $variableExports,
        VariablePlaceholderCollection $variableDependencies
    ) {
        foreach ($calls as $call) {
            $classDependencies = $classDependencies->merge([$call->getClassDependencies()]);
            $variableExports = $variableExports->merge([$call->getVariableExports()]);
        }

        return new CompilableSource($statements, $classDependencies, $variableExports, $variableDependencies);
    }
}
