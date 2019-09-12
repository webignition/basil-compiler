<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\ValueComparisonAssertionInterface;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;

abstract class AbstractTwoValueComparisonTranspiler implements TranspilerInterface
{
    protected $assertionCallFactory;
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

    abstract protected function getAssertionCall(
        ValueComparisonAssertionInterface $assertion,
        VariableAssignmentCall $examinedValue,
        VariableAssignmentCall $expectedValue
    ): TranspilationResultInterface;

    /**
     * @param ValueComparisonAssertionInterface $assertion
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     */
    protected function doTranspile(ValueComparisonAssertionInterface $assertion): TranspilationResultInterface
    {
        $examinedValue = $assertion->getExaminedValue();
        if (null === $examinedValue || !$this->assertableValueExaminer->isAssertableExaminedValue($examinedValue)) {
            throw new NonTranspilableModelException($assertion);
        }

        $expectedValue = $assertion->getExpectedValue();
        if (null === $expectedValue || !$this->assertableValueExaminer->isAssertableExpectedValue($expectedValue)) {
            throw new NonTranspilableModelException($assertion);
        }

        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $examinedValueAssignmentCall = $this->variableAssignmentCallFactory->createValueVariableAssignmentCall(
            $examinedValue,
            $examinedValuePlaceholder
        );

        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);
        $expectedValueAssignmentCall = $this->variableAssignmentCallFactory->createValueVariableAssignmentCall(
            $expectedValue,
            $expectedValuePlaceholder
        );

        if (null === $expectedValueAssignmentCall || null === $examinedValueAssignmentCall) {
            throw new NonTranspilableModelException($assertion);
        }

        return $this->getAssertionCall(
            (string) $assertion->getComparison(),
            $examinedValueAssignmentCall,
            $expectedValueAssignmentCall
        );
    }
}
