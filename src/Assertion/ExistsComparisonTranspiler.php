<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Assertion\AssertionInterface;
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
    private $assertableValueExaminer;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentCallFactory $variableAssignmentCallFactory,
        AssertableValueExaminer $assertableValueExaminer
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
        $this->assertableValueExaminer = $assertableValueExaminer;
    }

    public static function createTranspiler(): ExistsComparisonTranspiler
    {
        return new ExistsComparisonTranspiler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentCallFactory::createFactory(),
            AssertableValueExaminer::create()
        );
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof AssertionInterface) {
            return false;
        }

        return in_array($model->getComparison(), [
            AssertionComparisons::EXISTS,
            AssertionComparisons::NOT_EXISTS,
        ]);
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
        if (!$model instanceof AssertionInterface) {
            throw new NonTranspilableModelException($model);
        }

        $isHandledComparison = in_array($model->getComparison(), [
            AssertionComparisons::EXISTS,
            AssertionComparisons::NOT_EXISTS,
        ]);

        if (false === $isHandledComparison) {
            throw new NonTranspilableModelException($model);
        }

        $examinedValue = $model->getExaminedValue();
        if (null === $examinedValue || !$this->assertableValueExaminer->isAssertableExaminedValue($examinedValue)) {
            throw new NonTranspilableModelException($model);
        }

        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $examinedValueAssignmentCall = $this->variableAssignmentCallFactory->createValueExistenceAssignmentCall(
            $examinedValue,
            $examinedValuePlaceholder
        );

        if (null === $examinedValueAssignmentCall) {
            throw new NonTranspilableModelException($model);
        }

        return $model->getComparison() === AssertionComparisons::EXISTS
            ? $this->assertionCallFactory->createValueIsTrueAssertionCall($examinedValueAssignmentCall)
            : $this->assertionCallFactory->createValueIsFalseAssertionCall($examinedValueAssignmentCall);
    }
}
