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
     * @param VariablePlaceholderCollection $variablePlaceholders
     *
     * @return CompilableSourceInterface
     */
    public function compose(
        array $statements,
        array $calls,
        ClassDependencyCollection $classDependencies,
        VariablePlaceholderCollection $variablePlaceholders
    ) {
        foreach ($calls as $call) {
            $classDependencies = $classDependencies->merge([$call->getClassDependencies()]);
            $variablePlaceholders = $variablePlaceholders->merge([$call->getVariablePlaceholders()]);
        }

        return new CompilableSource($statements, $classDependencies, $variablePlaceholders);
    }
}
