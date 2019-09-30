<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilTranspiler\Model\TranspilableSource;
use webignition\BasilTranspiler\Model\TranspilableSourceInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\VariableNames;

class WebDriverElementInspectorCallFactory
{
    public static function createFactory(): WebDriverElementInspectorCallFactory
    {
        return new WebDriverElementInspectorCallFactory();
    }

    public function createGetValueCall(VariablePlaceholder $collectionPlaceholder): TranspilableSourceInterface
    {
        $variablePlaceholders = new VariablePlaceholderCollection();
        $variablePlaceholders = $variablePlaceholders->withAdditionalItems([
            $collectionPlaceholder,
        ]);

        $inspectorPlaceholder = $variablePlaceholders->create(VariableNames::WEBDRIVER_ELEMENT_INSPECTOR);

        $statements = [
            $inspectorPlaceholder . '->getValue(' . $collectionPlaceholder . ')',
        ];

        return new TranspilableSource($statements, new UseStatementCollection(), $variablePlaceholders);
    }
}
