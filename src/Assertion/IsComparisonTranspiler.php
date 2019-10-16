<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class IsComparisonTranspiler extends AbstractComparisonAssertionTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): IsComparisonTranspiler
    {
        return new IsComparisonTranspiler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof ComparisonAssertionInterface) {
            return false;
        }

        return in_array($model->getComparison(), [AssertionComparison::IS, AssertionComparison::IS_NOT]);
    }

    /**
     * @param object $model
     *
     * @return CompilableSourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): CompilableSourceInterface
    {
        if (!$model instanceof ComparisonAssertionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (!in_array($model->getComparison(), [AssertionComparison::IS, AssertionComparison::IS_NOT])) {
            throw new NonTranspilableModelException($model);
        }
        return $this->doTranspile($model);
    }

    protected function getAssertionCall(
        ComparisonAssertionInterface $assertion,
        CompilableSourceInterface $examinedValue,
        CompilableSourceInterface $expectedValue,
        VariablePlaceholder $examinedValuePlaceholder,
        VariablePlaceholder $expectedValuePlaceholder
    ): CompilableSourceInterface {
        return AssertionComparison::IS === $assertion->getComparison()
            ? $this->assertionCallFactory->createValuesAreEqualAssertionCall(
                $examinedValue,
                $expectedValue,
                $examinedValuePlaceholder,
                $expectedValuePlaceholder
            )
            : $this->assertionCallFactory->createValuesAreNotEqualAssertionCall(
                $examinedValue,
                $expectedValue,
                $examinedValuePlaceholder,
                $expectedValuePlaceholder
            );
    }
}
