<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\TranspilerInterface;

class IncludesComparisonTranspiler extends AbstractTwoValueComparisonTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): IncludesComparisonTranspiler
    {
        return new IncludesComparisonTranspiler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentCallFactory::createFactory(),
            AssertableValueExaminer::create()
        );
    }

    protected function getHandledComparisons(): array
    {
        return [
            AssertionComparisons::INCLUDES,
            AssertionComparisons::EXCLUDES,
        ];
    }

    protected function getAssertionCall(
        string $comparison,
        VariableAssignmentCall $examinedValue,
        VariableAssignmentCall $expectedValue
    ): TranspilationResultInterface {
        return $comparison === AssertionComparisons::INCLUDES
            ? $this->assertionCallFactory->createValueIncludesValueAssertionCall($expectedValue, $examinedValue)
            : $this->assertionCallFactory->createValueNotIncludesValueAssertionCall($expectedValue, $examinedValue);
    }
}
