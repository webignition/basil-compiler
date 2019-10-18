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

class MatchesComparisonTranspiler extends AbstractComparisonAssertionTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): MatchesComparisonTranspiler
    {
        return new MatchesComparisonTranspiler(
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

        return AssertionComparison::MATCHES === $model->getComparison();
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

        if (AssertionComparison::MATCHES !== $model->getComparison()) {
            throw new NonTranspilableModelException($model);
        }

        return $this->doTranspile($model);
    }

    protected function getAssertionTemplate(ComparisonAssertionInterface $assertion): string
    {
        return AssertionCallFactory::ASSERT_MATCHES_TEMPLATE;
    }
}
