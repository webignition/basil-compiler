<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\TranspilerInterface;

class IsComparisonTranspiler extends AbstractTwoValueComparisonTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): IsComparisonTranspiler
    {
        return new IsComparisonTranspiler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentCallFactory::createFactory(),
            AssertableValueExaminer::create()
        );
    }

    protected function getHandledComparisons(): array
    {
        return [
            AssertionComparisons::IS,
            AssertionComparisons::IS_NOT,
        ];
    }

    protected function getAssertionCall(
        string $comparison,
        VariableAssignmentCall $examinedValue,
        VariableAssignmentCall $expectedValue
    ): TranspilationResultInterface {
        return $comparison === AssertionComparisons::IS
            ? $this->assertionCallFactory->createValuesAreEqualAssertionCall($examinedValue, $expectedValue)
            : $this->assertionCallFactory->createValuesAreNotEqualAssertionCall($examinedValue, $expectedValue);
    }
}
