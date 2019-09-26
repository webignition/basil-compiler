<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
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
        if (!$model instanceof ComparisonAssertionInterface) {
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
     */
    public function transpile(object $model): TranspilationResultInterface
    {
        if (!$model instanceof ComparisonAssertionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (AssertionComparison::MATCHES !== $model->getComparison()) {
            throw new NonTranspilableModelException($model);
        }

        return $this->doTranspile($model);
    }

    protected function getAssertionCall(
        ComparisonAssertionInterface $assertion,
        VariableAssignmentCall $examinedValue,
        VariableAssignmentCall $expectedValue
    ): TranspilationResultInterface {
        return $this->assertionCallFactory->createValueMatchesValueAssertionCall($expectedValue, $examinedValue);
    }
}
