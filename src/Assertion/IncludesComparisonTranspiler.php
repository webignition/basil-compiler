<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\NamedDomIdentifierTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\Value\ValueTranspiler;

class IncludesComparisonTranspiler extends AbstractComparisonAssertionTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): IncludesComparisonTranspiler
    {
        return new IncludesComparisonTranspiler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentFactory::createFactory(),
            ValueTranspiler::createTranspiler(),
            NamedDomIdentifierTranspiler::createTranspiler()
        );
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof ComparisonAssertionInterface) {
            return false;
        }

        return in_array($model->getComparison(), [AssertionComparison::INCLUDES, AssertionComparison::EXCLUDES]);
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): SourceInterface
    {
        if (!$model instanceof ComparisonAssertionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (!in_array($model->getComparison(), [AssertionComparison::INCLUDES, AssertionComparison::EXCLUDES])) {
            throw new NonTranspilableModelException($model);
        }

        return $this->doTranspile($model);
    }

    protected function getAssertionTemplate(ComparisonAssertionInterface $assertion): string
    {
        return AssertionComparison::INCLUDES === $assertion->getComparison()
            ? AssertionCallFactory::ASSERT_STRING_CONTAINS_STRING_TEMPLATE
            : AssertionCallFactory::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE;
    }
}
