<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
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
    ): TranspilationResultInterface {
        $variablePlaceholders = new VariablePlaceholderCollection();
        $variablePlaceholders = $variablePlaceholders->withAdditionalItems([
            $collectionPlaceholder,
        ]);

        $mutatorPlaceholder = $variablePlaceholders->create(VariableNames::WEBDRIVER_ELEMENT_MUTATOR);

        $statements = [
            $mutatorPlaceholder . '->setValue(' . $collectionPlaceholder . ', ' . $valuePlaceholder . ')',
        ];

        return new TranspilationResult($statements, new UseStatementCollection(), $variablePlaceholders);
    }
}
