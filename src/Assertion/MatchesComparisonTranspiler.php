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

class MatchesComparisonTranspiler extends AbstractComparisonAssertionTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): MatchesComparisonTranspiler
    {
        return new MatchesComparisonTranspiler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentCallFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof AssertableComparisonAssertionInterface) {
            return false;
        }

        return AssertionComparison::MATCHES === $model->getComparison();
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

        if (AssertionComparison::MATCHES !== $model->getComparison()) {
            throw new NonTranspilableModelException($model);
        }

        return $this->doTranspile($model);
    }

    protected function getAssertionCall(
        AssertableComparisonAssertionInterface $assertion,
        VariableAssignmentCall $examinedValue,
        VariableAssignmentCall $expectedValue
    ): TranspilationResultInterface {
        return $this->assertionCallFactory->createValueMatchesValueAssertionCall($expectedValue, $examinedValue);
    }
}
