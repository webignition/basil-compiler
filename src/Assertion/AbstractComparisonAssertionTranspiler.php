<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;

abstract class AbstractComparisonAssertionTranspiler implements TranspilerInterface
{
    protected $assertionCallFactory;
    private $variableAssignmentCallFactory;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentCallFactory $variableAssignmentCallFactory
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
    }

    abstract protected function getAssertionCall(
        ComparisonAssertionInterface $assertion,
        VariableAssignmentCall $examinedValue,
        VariableAssignmentCall $expectedValue
    ): TranspilationResultInterface;

    /**
     * @param ComparisonAssertionInterface $assertion
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     */
    protected function doTranspile(ComparisonAssertionInterface $assertion): TranspilationResultInterface
    {
        $examinedValue = $assertion->getExaminedValue();
        $expectedValue = $assertion->getExpectedValue();

        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $examinedValueAssignmentCall = $this->variableAssignmentCallFactory->createForValue(
            $examinedValue,
            $examinedValuePlaceholder
        );

        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);
        $expectedValueAssignmentCall = $this->variableAssignmentCallFactory->createForValue(
            $expectedValue,
            $expectedValuePlaceholder
        );

        if (null === $expectedValueAssignmentCall || null === $examinedValueAssignmentCall) {
            throw new NonTranspilableModelException($assertion);
        }

        return $this->getAssertionCall(
            $assertion,
            $examinedValueAssignmentCall,
            $expectedValueAssignmentCall
        );
    }
}
