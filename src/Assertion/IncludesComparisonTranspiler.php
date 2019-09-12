<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\ExcludesAssertion;
use webignition\BasilModel\Assertion\IncludesAssertion;
use webignition\BasilModel\Assertion\ValueComparisonAssertionInterface;
use webignition\BasilModel\Exception\InvalidAssertionExaminedValueException;
use webignition\BasilModel\Exception\InvalidAssertionExpectedValueException;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class IncludesComparisonTranspiler extends AbstractTwoValueComparisonTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): IncludesComparisonTranspiler
    {
        return new IncludesComparisonTranspiler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentCallFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof IncludesAssertion || $model instanceof ExcludesAssertion;
    }

    /**
     * @param object $model
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     * @throws InvalidAssertionExaminedValueException
     * @throws InvalidAssertionExpectedValueException
     */
    public function transpile(object $model): TranspilationResultInterface
    {
        if (!($model instanceof IncludesAssertion || $model instanceof ExcludesAssertion)) {
            throw new NonTranspilableModelException($model);
        }

        return $this->doTranspile($model);
    }

    protected function getAssertionCall(
        ValueComparisonAssertionInterface $assertion,
        VariableAssignmentCall $examinedValue,
        VariableAssignmentCall $expectedValue
    ): TranspilationResultInterface {
        return $assertion instanceof IncludesAssertion
            ? $this->assertionCallFactory->createValueIncludesValueAssertionCall($expectedValue, $examinedValue)
            : $this->assertionCallFactory->createValueNotIncludesValueAssertionCall($expectedValue, $examinedValue);
    }

}
