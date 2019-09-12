<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\ExistsAssertion;
use webignition\BasilModel\Assertion\NotExistsAssertion;
use webignition\BasilModel\Exception\InvalidAssertionExaminedValueException;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;

class ExistsComparisonTranspiler implements TranspilerInterface
{
    private $assertionCallFactory;
    private $variableAssignmentCallFactory;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentCallFactory $variableAssignmentCallFactory
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
    }

    public static function createTranspiler(): ExistsComparisonTranspiler
    {
        return new ExistsComparisonTranspiler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentCallFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof ExistsAssertion || $model instanceof NotExistsAssertion;
    }

    /**
     * @param object $model
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     * @throws InvalidAssertionExaminedValueException
     */
    public function transpile(object $model): TranspilationResultInterface
    {
        if (!($model instanceof ExistsAssertion || $model instanceof NotExistsAssertion)) {
            throw new NonTranspilableModelException($model);
        }

        $examinedValue = $model->getExaminedValue()->getExaminedValue();

        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $examinedValueAssignmentCall = $this->variableAssignmentCallFactory->createValueExistenceAssignmentCall(
            $examinedValue,
            $examinedValuePlaceholder
        );

        if (null === $examinedValueAssignmentCall) {
            throw new NonTranspilableModelException($model);
        }

        return $model instanceof ExistsAssertion
            ? $this->assertionCallFactory->createValueIsTrueAssertionCall($examinedValueAssignmentCall)
            : $this->assertionCallFactory->createValueIsFalseAssertionCall($examinedValueAssignmentCall);
    }
}
