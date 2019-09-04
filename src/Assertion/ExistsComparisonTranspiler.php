<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\CallFactory\ElementLocatorCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilationResultComposer;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;

class ExistsComparisonTranspiler implements TranspilerInterface
{
    private $assertionCallFactory;
    private $variableAssignmentCallFactory;
    private $valueTranspiler;
    private $domCrawlerNavigatorCallFactory;
    private $elementLocatorCallFactory;
    private $assertableValueExaminer;
    private $phpUnitTestCasePlaceholder;
    private $transpilationResultComposer;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentCallFactory $variableAssignmentCallFactory,
        ValueTranspiler $valueTranspiler,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        ElementLocatorCallFactory $elementLocatorCallFactory,
        AssertableValueExaminer $assertableValueExaminer,
        TranspilationResultComposer $transpilationResultComposer
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
        $this->valueTranspiler = $valueTranspiler;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->assertableValueExaminer = $assertableValueExaminer;
        $this->transpilationResultComposer = $transpilationResultComposer;

        $this->phpUnitTestCasePlaceholder = new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE);
    }

    public static function createTranspiler(): ExistsComparisonTranspiler
    {
        return new ExistsComparisonTranspiler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentCallFactory::createFactory(),
            ValueTranspiler::createTranspiler(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            ElementLocatorCallFactory::createFactory(),
            AssertableValueExaminer::create(),
            TranspilationResultComposer::create()
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
        if (!$this->assertableValueExaminer->isAssertableExaminedValue($examinedValue)) {
            throw new NonTranspilableModelException($model);
        }

        $examinedValuePlaceholder = new VariablePlaceholder('EXAMINED_VALUE');
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
