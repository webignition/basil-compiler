<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilTranspiler\VariableNames;

class WebDriverElementInspectorCallFactory
{
    public static function createFactory(): WebDriverElementInspectorCallFactory
    {
        return new WebDriverElementInspectorCallFactory();
    }

    public function createGetValueCall(VariablePlaceholder $collectionPlaceholder): SourceInterface
    {
        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->withAdditionalItems([
            $collectionPlaceholder,
        ]);

        $variableDependencies = new VariablePlaceholderCollection();
        $inspectorPlaceholder = $variableDependencies->create(VariableNames::WEBDRIVER_ELEMENT_INSPECTOR);

        $compilationMetadata = (new Metadata())
            ->withAdditionalVariableDependencies($variableDependencies)
            ->withVariableExports($variableExports);

        $compilableSource = (new Source())
            ->withStatements([
                $inspectorPlaceholder . '->getValue(' . $collectionPlaceholder . ')',
            ])
            ->withMetadata($compilationMetadata);

        return $compilableSource;
    }
}
