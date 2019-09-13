<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\AssertableComparisonAssertionInterface;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Exception\InvalidAssertableExaminedValueException;
use webignition\BasilModel\Exception\InvalidAssertableExpectedValueException;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class IsComparisonTranspiler extends AbstractComparisonAssertionTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): IsComparisonTranspiler
    {
        return new IsComparisonTranspiler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentCallFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof AssertableComparisonAssertionInterface) {
            return false;
        }

        return in_array($model->getComparison(), [AssertionComparison::IS, AssertionComparison::IS_NOT]);
    }

    /**
     * @param object $model
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     * @throws InvalidAssertableExaminedValueException
     * @throws InvalidAssertableExpectedValueException
     */
    public function transpile(object $model): TranspilationResultInterface
    {
        if (!$model instanceof AssertableComparisonAssertionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (!in_array($model->getComparison(), [AssertionComparison::IS, AssertionComparison::IS_NOT])) {
            throw new NonTranspilableModelException($model);
        }
        return $this->doTranspile($model);
    }

    protected function getAssertionCall(
        AssertableComparisonAssertionInterface $assertion,
        VariableAssignmentCall $examinedValue,
        VariableAssignmentCall $expectedValue
    ): TranspilationResultInterface {
        return AssertionComparison::IS === $assertion->getComparison()
            ? $this->assertionCallFactory->createValuesAreEqualAssertionCall($examinedValue, $expectedValue)
            : $this->assertionCallFactory->createValuesAreNotEqualAssertionCall($examinedValue, $expectedValue);
    }
}
