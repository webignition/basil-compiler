<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
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
        $variablePlaceholders = new VariablePlaceholderCollection();
        $variablePlaceholders = $variablePlaceholders->withAdditionalItems([
            $collectionPlaceholder,
        ]);

        $inspectorPlaceholder = $variablePlaceholders->create(VariableNames::WEBDRIVER_ELEMENT_INSPECTOR);

        $statements = [
            $inspectorPlaceholder . '->getValue(' . $collectionPlaceholder . ')',
        ];

        return new CompilableSource($statements, new ClassDependencyCollection(), $variablePlaceholders);
    }
}
