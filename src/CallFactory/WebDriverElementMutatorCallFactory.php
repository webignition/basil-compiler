<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\CompilationMetadata;
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
        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->withAdditionalItems([
            $collectionPlaceholder,
            $valuePlaceholder,
        ]);

        $variableDependencies = new VariablePlaceholderCollection();
        $mutatorPlaceholder = $variableDependencies->create(VariableNames::WEBDRIVER_ELEMENT_MUTATOR);

        $statements = [
            $mutatorPlaceholder . '->setValue(' . $collectionPlaceholder . ', ' . $valuePlaceholder . ')',
        ];

        $compilationMetadata = (new CompilationMetadata())
            ->withVariableDependencies($variableDependencies)
            ->withVariableExports($variableExports);

        return new CompilableSource($statements, $compilationMetadata);
    }
}
