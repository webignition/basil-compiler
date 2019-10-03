<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\CompilationMetadata;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\VariableNames;

class WebDriverElementInspectorCallFactory
{
    public static function createFactory(): WebDriverElementInspectorCallFactory
    {
        return new WebDriverElementInspectorCallFactory();
    }

    public function createGetValueCall(VariablePlaceholder $collectionPlaceholder): CompilableSourceInterface
    {
        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->withAdditionalItems([
            $collectionPlaceholder,
        ]);

        $variableDependencies = new VariablePlaceholderCollection();
        $inspectorPlaceholder = $variableDependencies->create(VariableNames::WEBDRIVER_ELEMENT_INSPECTOR);

        $compilationMetadata = (new CompilationMetadata())
            ->withAdditionalVariableDependencies($variableDependencies)
            ->withVariableExports($variableExports);

        $compilableSource = new CompilableSource(
            [
                $inspectorPlaceholder . '->getValue(' . $collectionPlaceholder . ')',
            ],
            $compilationMetadata
        );

        return $compilableSource;
    }
}
