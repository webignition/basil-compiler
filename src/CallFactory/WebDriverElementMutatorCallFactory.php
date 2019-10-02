<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\VariableNames;

class WebDriverElementMutatorCallFactory
{
    public static function createFactory(): WebDriverElementMutatorCallFactory
    {
        return new WebDriverElementMutatorCallFactory();
    }

    public function createSetValueCall(
        VariablePlaceholder $collectionPlaceholder,
        VariablePlaceholder $valuePlaceholder
    ): CompilableSourceInterface {
        $variablePlaceholders = new VariablePlaceholderCollection();
        $variablePlaceholders = $variablePlaceholders->withAdditionalItems([
            $collectionPlaceholder,
        ]);

        $variableDependencies = new VariablePlaceholderCollection();
        $mutatorPlaceholder = $variableDependencies->create(VariableNames::WEBDRIVER_ELEMENT_MUTATOR);

        $statements = [
            $mutatorPlaceholder . '->setValue(' . $collectionPlaceholder . ', ' . $valuePlaceholder . ')',
        ];

        return new CompilableSource(
            $statements,
            new ClassDependencyCollection(),
            $variablePlaceholders,
            $variableDependencies
        );
    }
}
