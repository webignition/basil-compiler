<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\Model\NamedDomIdentifierValue;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;

abstract class AbstractComparisonAssertionTranspiler implements TranspilerInterface
{
    protected $assertionCallFactory;
    private $variableAssignmentFactory;
    private $valueTranspiler;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentFactory $variableAssignmentFactory,
        ValueTranspiler $valueTranspiler
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->valueTranspiler = $valueTranspiler;
    }

    abstract protected function getAssertionTemplate(ComparisonAssertionInterface $assertion): string;

    /**
     * @param ComparisonAssertionInterface $assertion
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    protected function doTranspile(ComparisonAssertionInterface $assertion): SourceInterface
    {
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);

        $examinedValue = $assertion->getExaminedValue();
        $expectedValue = $assertion->getExpectedValue();

        if ($examinedValue instanceof DomIdentifierValueInterface) {
            $examinedValue = new NamedDomIdentifierValue($examinedValue, $examinedValuePlaceholder);
        }

        if ($expectedValue instanceof DomIdentifierValueInterface) {
            $expectedValue = new NamedDomIdentifierValue($expectedValue, $expectedValuePlaceholder);
        }

        $examinedValueAccessor = $this->valueTranspiler->transpile($examinedValue);
        $expectedValueAccessor = $this->valueTranspiler->transpile($expectedValue);

        $examinedValueAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $examinedValueAccessor,
            $examinedValuePlaceholder
        );

        $expectedValueAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $expectedValueAccessor,
            $expectedValuePlaceholder
        );

        return $this->assertionCallFactory->createValueComparisonAssertionCall(
            $expectedValueAssignment,
            $examinedValueAssignment,
            $expectedValuePlaceholder,
            $examinedValuePlaceholder,
            $this->getAssertionTemplate($assertion)
        );
    }
}
