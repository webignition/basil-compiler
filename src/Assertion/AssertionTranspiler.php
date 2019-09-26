<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilModel\Assertion\ExaminationAssertionInterface;
use webignition\BasilTranspiler\AbstractDelegatingTranspiler;
use webignition\BasilTranspiler\TranspilerInterface;

class AssertionTranspiler extends AbstractDelegatingTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): AssertionTranspiler
    {
        return new AssertionTranspiler(
            [
                ExistsComparisonTranspiler::createTranspiler(),
                IsComparisonTranspiler::createTranspiler(),
                IncludesComparisonTranspiler::createTranspiler(),
                MatchesComparisonTranspiler::createTranspiler(),
            ]
        );
    }

    public function handles(object $model): bool
    {
        if ($model instanceof ExaminationAssertionInterface || $model instanceof ComparisonAssertionInterface) {
            return null !== $this->findDelegatedTranspiler($model);
        }

        return false;
    }
}
