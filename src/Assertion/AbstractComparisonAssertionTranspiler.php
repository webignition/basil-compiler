<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
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
    ): CompilableSourceInterface;

    /**
     * @param ComparisonAssertionInterface $assertion
     *
     * @return CompilableSourceInterface
     *
     * @throws NonTranspilableModelException
     */
    protected function doTranspile(ComparisonAssertionInterface $assertion): CompilableSourceInterface
    {
        $examinedValue = $assertion->getExaminedValue();
        $expectedValue = $assertion->getExpectedValue();

        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $examinedValueAssignment = $this->variableAssignmentCallFactory->createForValue(
            $examinedValue,
            $examinedValuePlaceholder
        );

        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);
        $expectedValueAssignment = $this->variableAssignmentCallFactory->createForValue(
            $expectedValue,
            $expectedValuePlaceholder
        );

        if (null === $expectedValueAssignment || null === $examinedValueAssignment) {
            throw new NonTranspilableModelException($assertion);
        }

        return $this->getAssertionCall(
            $assertion,
            $examinedValueAssignment,
            $expectedValueAssignment
        );
    }
}
