<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Assertion;

use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Value\AttributeValueInterface;
use webignition\BasilModel\Value\ElementValueInterface;
use webignition\BasilModel\Value\EnvironmentValueInterface;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\ElementLocatorCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;

class ExistsComparisonTranspiler implements TranspilerInterface
{
    const ELEMENT_EXISTS_TEMPLATE = '%s->assertTrue(%s)';
    const ATTRIBUTE_EXISTS_TEMPLATE = '%s->assertNotNull(%s->getAttribute(\'%s\'))';
    const VARIABLE_EXISTS_TEMPLATE = '%s->assertNotNull(%s)';

    private $valueTranspiler;
    private $domCrawlerNavigatorCallFactory;
    private $elementLocatorCallFactory;
    private $phpUnitTestCasePlaceholder;

    public function __construct(
        ValueTranspiler $valueTranspiler,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        ElementLocatorCallFactory $elementLocatorCallFactory
    ) {
        $this->valueTranspiler = $valueTranspiler;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;

        $this->phpUnitTestCasePlaceholder = new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE);
    }

    public static function createTranspiler(): ExistsComparisonTranspiler
    {
        return new ExistsComparisonTranspiler(
            ValueTranspiler::createTranspiler(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            ElementLocatorCallFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof AssertionInterface) {
            return false;
        }

        return AssertionComparisons::EXISTS === $model->getComparison();
    }

    /**
     * @param object $model
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): TranspilationResult
    {
        if (!$model instanceof AssertionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (AssertionComparisons::EXISTS !== $model->getComparison()) {
            throw new NonTranspilableModelException($model);
        }

        $examinedValue = $model->getExaminedValue();
        if (!$this->isAssertableExaminedValue($examinedValue)) {
            throw new NonTranspilableModelException($model);
        }

        // examined value types:
        // ✓ element value
        // ✓ attribute value
        // browser object
        // ✓ environment
        // page object

        if ($examinedValue instanceof ElementValueInterface) {
            return $this->transpileForElementValue($examinedValue);
        }

        if ($examinedValue instanceof AttributeValueInterface) {
            return $this->transpileForAttributeValue($examinedValue);
        }

        if ($examinedValue instanceof EnvironmentValueInterface) {
            return $this->transpileForEnvironmentValue($examinedValue);
        }

        throw new NonTranspilableModelException($model);
    }

    private function isAssertableExaminedValue(?object $value = null): bool
    {
        if ($value instanceof ElementValueInterface) {
            return true;
        }

        if ($value instanceof AttributeValueInterface) {
            return true;
        }

        if ($value instanceof EnvironmentValueInterface) {
            return true;
        }

        if ($value instanceof ObjectValueInterface) {
            if (in_array($value->getType(), [ValueTypes::BROWSER_OBJECT_PROPERTY, ValueTypes::PAGE_OBJECT_PROPERTY])) {
                return true;
            }
        }

        return false;
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

    /**
     * @param ElementValueInterface $elementValue
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    private function transpileForElementValue(ElementValueInterface $elementValue): TranspilationResult
    {
        $hasElementCall = $this->domCrawlerNavigatorCallFactory->createHasElementCallForIdentifier(
            $elementValue->getIdentifier()
        );

        return $this->createElementExistsAssertionCall($hasElementCall);
    }

    /**
     * @param AttributeValueInterface $attributeValue
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    private function transpileForAttributeValue(AttributeValueInterface $attributeValue): TranspilationResult
    {
        $attributeIdentifier = $attributeValue->getIdentifier();
        $elementIdentifier = $attributeIdentifier->getElementIdentifier();

        $elementLocatorPlaceholder = new VariablePlaceholder('ELEMENT_LOCATOR');
        $elementPlaceholder = new VariablePlaceholder('ELEMENT');
        $phpunitTesCasePlaceholder = new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE);

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
        $assertionStatement = sprintf(
            self::ATTRIBUTE_EXISTS_TEMPLATE,
            (string) $phpunitTesCasePlaceholder,
            $elementPlaceholder,
            $attributeIdentifier->getAttributeName()
        );

        $statements = [
            $elementLocatorConstructorStatement,
            $elementExistsStatement,
            $elementFindStatement,
            $assertionStatement
        ];

        $calls = [
            $elementLocatorConstructor,
            $hasElementCall,
            $findElementCall,
            $elementExistsAssertionCall,
        ];

        return $this->createTranspilationResult(
            $statements,
            $calls,
            new UseStatementCollection(),
            new VariablePlaceholderCollection([
                $elementLocatorPlaceholder,
                $elementPlaceholder,
                $phpunitTesCasePlaceholder,
            ])
        );
    }

    /**
     * @param EnvironmentValueInterface $environmentValue
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    private function transpileForEnvironmentValue(EnvironmentValueInterface $environmentValue): TranspilationResult
    {
        $variablePlaceholder = new VariablePlaceholder('ENVIRONMENT_VARIABLE');

        $environmentVariableAccessCall = $this->valueTranspiler->transpile($environmentValue);
        $variableCreationCall = $environmentVariableAccessCall->extend(
            sprintf(
                '%s = %s ?? null',
                (string) $variablePlaceholder,
                '%s'
            ),
            new UseStatementCollection(),
            new VariablePlaceholderCollection()
        );

        $variableCreationStatement = (string) $variableCreationCall;

        $assertionStatement = sprintf(
            self::VARIABLE_EXISTS_TEMPLATE,
            (string) $this->phpUnitTestCasePlaceholder,
            (string) $variablePlaceholder
        );

        $statements = [
            $variableCreationStatement,
            $assertionStatement,
        ];

        $calls = [
            $environmentVariableAccessCall,
            $variableCreationCall,
        ];

        return $this->createTranspilationResult(
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
    private function createTranspilationResult(
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
}
