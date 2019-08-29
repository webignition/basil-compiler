<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\AttributeValueInterface;
use webignition\BasilModel\Value\ElementValueInterface;
use webignition\BasilModel\Value\EnvironmentValueInterface;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\ElementLocatorCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\UnknownItemException;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;

class ExistsComparisonTranspiler implements TranspilerInterface
{
    const ELEMENT_EXISTS_TEMPLATE = '%s->assertTrue(%s)';
    const VARIABLE_EXISTS_TEMPLATE = '%s->assertNotNull(%s)';
    const ELEMENT_NOT_EXISTS_TEMPLATE = '%s->assertFalse(%s)';
    const VARIABLE_NOT_EXISTS_TEMPLATE = '%s->assertNull(%s)';

    private $valueTranspiler;
    private $domCrawlerNavigatorCallFactory;
    private $elementLocatorCallFactory;
    private $assertableValueExaminer;
    private $phpUnitTestCasePlaceholder;

    /**
     * @var string
     */
    private $attributeExistsTemplate = '';

    /**
     * @var string
     */
    private $attributeNotExistsTemplate = '';

    public function __construct(
        ValueTranspiler $valueTranspiler,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        ElementLocatorCallFactory $elementLocatorCallFactory,
        AssertableValueExaminer $assertableValueExaminer
    ) {
        $this->valueTranspiler = $valueTranspiler;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->assertableValueExaminer = $assertableValueExaminer;

        $this->phpUnitTestCasePlaceholder = new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE);
        $this->attributeExistsTemplate = sprintf(
            self::VARIABLE_EXISTS_TEMPLATE,
            '%s',
            '%s->getAttribute(\'%s\')'
        );

        $this->attributeNotExistsTemplate = sprintf(
            self::VARIABLE_NOT_EXISTS_TEMPLATE,
            '%s',
            '%s->getAttribute(\'%s\')'
        );
    }

    public static function createTranspiler(): ExistsComparisonTranspiler
    {
        return new ExistsComparisonTranspiler(
            ValueTranspiler::createTranspiler(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            ElementLocatorCallFactory::createFactory(),
            AssertableValueExaminer::create()
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
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     * @throws UnknownItemException
     */
    public function transpile(object $model): TranspilationResult
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

        if ($examinedValue instanceof ElementValueInterface) {
            return $this->transpileForElementValue($examinedValue, (string) $model->getComparison());
        }

        if ($examinedValue instanceof AttributeValueInterface) {
            return $this->transpileForAttributeValue($examinedValue, (string) $model->getComparison());
        }

        if ($examinedValue instanceof EnvironmentValueInterface) {
            return $this->transpileForScalarValue(
                $examinedValue,
                'ENVIRONMENT_VARIABLE',
                '%s = %s ?? null',
                (string) $model->getComparison()
            );
        }

        if ($examinedValue instanceof ObjectValueInterface) {
            if (ObjectNames::BROWSER === $examinedValue->getObjectName()) {
                return $this->transpileForScalarValue(
                    $examinedValue,
                    'BROWSER_VARIABLE',
                    '%s = %s',
                    (string) $model->getComparison()
                );
            }

            if (ObjectNames::PAGE === $examinedValue->getObjectName()) {
                return $this->transpileForScalarValue(
                    $examinedValue,
                    'PAGE_VARIABLE',
                    '%s = %s',
                    (string) $model->getComparison()
                );
            }
        }

        throw new NonTranspilableModelException($model);
    }

    private function createElementExistsAssertionCall(TranspilationResult $hasElementCall): TranspilationResult
    {
        $template = sprintf(
            self::ELEMENT_EXISTS_TEMPLATE,
            (string) $this->phpUnitTestCasePlaceholder,
            '%s'
        );

        return $hasElementCall->extend(
            $template,
            new UseStatementCollection(),
            new VariablePlaceholderCollection([
                $this->phpUnitTestCasePlaceholder,
            ])
        );
    }

    private function createElementExistenceAssertionCall(
        TranspilationResult $hasElementCall,
        string $template
    ): TranspilationResult {
        return $hasElementCall->extend(
            sprintf(
                $template,
                (string) $this->phpUnitTestCasePlaceholder,
                '%s'
            ),
            new UseStatementCollection(),
            new VariablePlaceholderCollection([
                $this->phpUnitTestCasePlaceholder,
            ])
        );
    }

    /**
     * @param ElementValueInterface $elementValue
     * @param string $comparison
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     * @throws UnknownItemException
     */
    private function transpileForElementValue(
        ElementValueInterface $elementValue,
        string $comparison
    ): TranspilationResult {
        $hasElementCall = $this->domCrawlerNavigatorCallFactory->createHasElementCallForIdentifier(
            $elementValue->getIdentifier()
        );

        $template = AssertionComparisons::EXISTS === $comparison
            ? self::ELEMENT_EXISTS_TEMPLATE
            : self::ELEMENT_NOT_EXISTS_TEMPLATE;

        return $this->createElementExistenceAssertionCall($hasElementCall, $template);
    }

    /**
     * @param AttributeValueInterface $attributeValue
     * @param string $comparison
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     * @throws UnknownItemException
     */
    private function transpileForAttributeValue(
        AttributeValueInterface $attributeValue,
        string $comparison
    ): TranspilationResult {
        $attributeIdentifier = $attributeValue->getIdentifier();
        $elementIdentifier = $attributeIdentifier->getElementIdentifier();

        $elementVariableAssignmentCall = $this->createElementVariableAssignmentCall($elementIdentifier);
        $elementVariableAssignmentCallPlaceholders = $elementVariableAssignmentCall->getVariablePlaceholders();

        $elementPlaceholder = $elementVariableAssignmentCallPlaceholders->get('ELEMENT');
        $phpunitTesCasePlaceholder = $elementVariableAssignmentCallPlaceholders->get(VariableNames::PHPUNIT_TEST_CASE);

        $assertionStatementTemplate = AssertionComparisons::EXISTS === $comparison
            ? $this->attributeExistsTemplate
            : $this->attributeNotExistsTemplate;

        $assertionStatement = sprintf(
            $assertionStatementTemplate,
            (string) $phpunitTesCasePlaceholder,
            $elementPlaceholder,
            $attributeIdentifier->getAttributeName()
        );

        $statements = array_merge(
            $elementVariableAssignmentCall->getLines(),
            [
                $assertionStatement,
            ]
        );

        $calls = [
            $elementVariableAssignmentCall,
        ];

        return $this->composeTranspilationResult(
            $statements,
            $calls,
            new UseStatementCollection(),
            new VariablePlaceholderCollection()
        );
    }

    /**
     * @param ValueInterface $value
     * @param string $examinedVariableName
     * @param string $accessCallTemplate
     * @param string $comparison
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    private function transpileForScalarValue(
        ValueInterface $value,
        string $examinedVariableName,
        string $accessCallTemplate,
        string $comparison
    ): TranspilationResult {
        $variablePlaceholder = new VariablePlaceholder($examinedVariableName);

        $variableAccessCall = $this->valueTranspiler->transpile($value);
        $variableCreationCall = $variableAccessCall->extend(
            sprintf(
                $accessCallTemplate,
                (string) $variablePlaceholder,
                '%s'
            ),
            new UseStatementCollection(),
            new VariablePlaceholderCollection()
        );

        $variableCreationStatement = (string) $variableCreationCall;

        $assertionStatementTemplate = AssertionComparisons::EXISTS === $comparison
            ? self::VARIABLE_EXISTS_TEMPLATE
            : self::VARIABLE_NOT_EXISTS_TEMPLATE;

        $assertionStatement = sprintf(
            $assertionStatementTemplate,
            (string) $this->phpUnitTestCasePlaceholder,
            (string) $variablePlaceholder
        );

        $statements = [
            $variableCreationStatement,
            $assertionStatement,
        ];

        $calls = [
            $variableAccessCall,
            $variableCreationCall,
        ];

        return $this->composeTranspilationResult(
            $statements,
            $calls,
            new UseStatementCollection(),
            new VariablePlaceholderCollection([
                $this->phpUnitTestCasePlaceholder,
            ])
        );
    }

    /**
     * @param string[] $statements
     * @param TranspilationResult[] $calls
     * @param UseStatementCollection $useStatements
     * @param VariablePlaceholderCollection $variablePlaceholders
     *
     * @return TranspilationResult
     */
    private function composeTranspilationResult(
        array $statements,
        array $calls,
        UseStatementCollection $useStatements,
        VariablePlaceholderCollection $variablePlaceholders
    ) {
        foreach ($calls as $call) {
            $useStatements = $useStatements->merge([$call->getUseStatements()]);
            $variablePlaceholders = $variablePlaceholders->merge([$call->getVariablePlaceholders()]);
        }

        return new TranspilationResult($statements, $useStatements, $variablePlaceholders);
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     * @throws UnknownItemException
     */
    private function createElementVariableAssignmentCall(ElementIdentifierInterface $elementIdentifier)
    {
        $variablePlaceholders = new VariablePlaceholderCollection();

        $elementLocatorPlaceholder = $variablePlaceholders->create('ELEMENT_LOCATOR');
        $elementPlaceholder = $variablePlaceholders->create('ELEMENT');

        $elementLocatorConstructor = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $hasElementCall = $this->domCrawlerNavigatorCallFactory->createHasElementCallForTranspiledArguments(
            new TranspilationResult(
                [(string) $elementLocatorPlaceholder],
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            )
        );

        $findElementCall = $this->domCrawlerNavigatorCallFactory->createFindElementCallForTranspiledArguments(
            new TranspilationResult(
                [(string) $elementLocatorPlaceholder],
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            )
        );

        $elementExistsAssertionCall = $this->createElementExistsAssertionCall($hasElementCall);

        $elementLocatorConstructorStatement = $elementLocatorPlaceholder . ' = ' . $elementLocatorConstructor;
        $elementExistsStatement = (string) $elementExistsAssertionCall;
        $elementFindStatement = $elementPlaceholder . ' = ' . $findElementCall;

        $statements = [
            $elementLocatorConstructorStatement,
            $elementExistsStatement,
            $elementFindStatement,
        ];

        $calls = [
            $elementLocatorConstructor,
            $hasElementCall,
            $findElementCall,
            $elementExistsAssertionCall,
        ];

        return $this->composeTranspilationResult(
            $statements,
            $calls,
            new UseStatementCollection(),
            $variablePlaceholders
        );
    }
}
