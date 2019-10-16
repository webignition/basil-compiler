<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentFactory;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\NonTranspilableValueException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;

abstract class AbstractComparisonAssertionTranspiler implements TranspilerInterface
{
    protected $assertionCallFactory;
    private $variableAssignmentFactory;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentFactory $variableAssignmentFactory
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->variableAssignmentFactory = $variableAssignmentFactory;
    }

    abstract protected function getAssertionCall(
        ComparisonAssertionInterface $assertion,
        CompilableSourceInterface $examinedValue,
        CompilableSourceInterface $expectedValue
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
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);

        try {
            $examinedValueAssignment = $this->variableAssignmentFactory->createForValue(
                $examinedValue,
                $examinedValuePlaceholder
            );

            $expectedValueAssignment = $this->variableAssignmentFactory->createForValue(
                $expectedValue,
                $expectedValuePlaceholder
            );
        } catch (NonTranspilableValueException $nonTranspilableValueException) {
            throw new NonTranspilableModelException($assertion);
        }

        return $this->getAssertionCall(
            $assertion,
            $examinedValueAssignment,
            $expectedValueAssignment
        );
    }
}
