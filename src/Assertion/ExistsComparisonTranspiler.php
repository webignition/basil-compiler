<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ExaminationAssertionInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\CallFactory\ElementCallArgumentFactory;
use webignition\BasilTranspiler\Model\NamedDomIdentifierValue;
use webignition\BasilTranspiler\NamedDomIdentifierTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\ObjectValueTypeExaminer;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;

class ExistsComparisonTranspiler implements TranspilerInterface
{
    private $assertionCallFactory;
    private $objectValueTypeExaminer;
    private $valueTranspiler;
    private $domCrawlerNavigatorCallFactory;
    private $namedDomIdentifierTranspiler;
    private $elementCallArgumentFactory;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        ObjectValueTypeExaminer $objectValueTypeExaminer,
        ValueTranspiler $valueTranspiler,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        NamedDomIdentifierTranspiler $namedDomIdentifierTranspiler,
        ElementCallArgumentFactory $elementCallArgumentFactory
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->objectValueTypeExaminer = $objectValueTypeExaminer;
        $this->valueTranspiler = $valueTranspiler;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->namedDomIdentifierTranspiler = $namedDomIdentifierTranspiler;
        $this->elementCallArgumentFactory = $elementCallArgumentFactory;
    }

    public static function createTranspiler(): ExistsComparisonTranspiler
    {
        return new ExistsComparisonTranspiler(
            AssertionCallFactory::createFactory(),
            ObjectValueTypeExaminer::createExaminer(),
            ValueTranspiler::createTranspiler(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            NamedDomIdentifierTranspiler::createTranspiler(),
            ElementCallArgumentFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof ExaminationAssertionInterface) {
            return false;
        }

        return in_array($model->getComparison(), [AssertionComparison::EXISTS, AssertionComparison::NOT_EXISTS]);
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
        if (!$model instanceof ExaminationAssertionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (!in_array($model->getComparison(), [AssertionComparison::EXISTS, AssertionComparison::NOT_EXISTS])) {
            throw new NonTranspilableModelException($model);
        }

        $value = $model->getExaminedValue();
        $valuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);

        $isScalarValue = $this->objectValueTypeExaminer->isOfType($value, [
            ObjectValueType::BROWSER_PROPERTY,
            ObjectValueType::ENVIRONMENT_PARAMETER,
            ObjectValueType::PAGE_PROPERTY,
        ]);

        $existence = null;

        if ($isScalarValue) {
            $accessor = $this->valueTranspiler->transpile($value);
            $accessor->appendStatement(0, ' ?? null');

            $assignment = clone $accessor;
            $assignment->prependStatement(-1, $valuePlaceholder . ' = ');

            $existence = (new Source())
                ->withPredecessors([$assignment])
                ->withStatements([
                    sprintf('%s = %s !== null', $valuePlaceholder, $valuePlaceholder)
                ]);

            return $this->createAssertionCall($model->getComparison(), $existence, $valuePlaceholder);
        }

        if ($value instanceof DomIdentifierValueInterface) {
            $identifier = $value->getIdentifier();

            if (null === $identifier->getAttributeName()) {
                $arguments = $this->elementCallArgumentFactory->createElementCallArguments($identifier);
                $accessor = $this->domCrawlerNavigatorCallFactory->createHasCall($arguments);

                $assignment = clone $accessor;
                $assignment->prependStatement(-1, $valuePlaceholder . ' = ');

                return $this->createAssertionCall($model->getComparison(), $assignment, $valuePlaceholder);
            }

            $accessor = $this->namedDomIdentifierTranspiler->transpile(
                new NamedDomIdentifierValue($value, $valuePlaceholder)
            );

            $existence = (new Source())
                ->withPredecessors([$accessor])
                ->withStatements([
                    sprintf('%s = %s !== null', $valuePlaceholder, $valuePlaceholder)
                ]);

            return $this->createAssertionCall($model->getComparison(), $existence, $valuePlaceholder);
        }

        throw new NonTranspilableModelException($model);
    }

    private function createAssertionCall(
        string $comparison,
        SourceInterface $source,
        VariablePlaceholder $valuePlaceholder
    ): SourceInterface {
        $assertionTemplate = AssertionComparison::EXISTS === $comparison
            ? AssertionCallFactory::ASSERT_TRUE_TEMPLATE
            : AssertionCallFactory::ASSERT_FALSE_TEMPLATE;

        return $this->assertionCallFactory->createValueExistenceAssertionCall(
            $source,
            $valuePlaceholder,
            $assertionTemplate
        );
    }
}
