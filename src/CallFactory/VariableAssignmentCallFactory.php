<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\SingleQuotedStringEscaper;
use webignition\BasilTranspiler\TranspilationResultComposer;
use webignition\BasilTranspiler\Value\ValueTranspiler;

class VariableAssignmentCallFactory
{
    const DEFAULT_ELEMENT_LOCATOR_PLACEHOLDER_NAME = 'ELEMENT_LOCATOR';
    const DEFAULT_ATTRIBUTE_PLACEHOLDER_NAME = 'ATTRIBUTE';
    const DEFAULT_ELEMENT_PLACEHOLDER_NAME = 'ELEMENT';
    const DEFAULT_COLLECTION_PLACEHOLDER_NAME = 'COLLECTION';

    private $assertionCallFactory;
    private $elementLocatorCallFactory;
    private $domCrawlerNavigatorCallFactory;
    private $transpilationResultComposer;
    private $valueTranspiler;
    private $singleQuotedStringEscaper;
    private $webDriverElementInspectorCallFactory;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        ElementLocatorCallFactory $elementLocatorCallFactory,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        TranspilationResultComposer $transpilationResultComposer,
        ValueTranspiler $valueTranspiler,
        SingleQuotedStringEscaper $singleQuotedStringEscaper,
        WebDriverElementInspectorCallFactory $webDriverElementInspectorCallFactory
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->transpilationResultComposer = $transpilationResultComposer;
        $this->valueTranspiler = $valueTranspiler;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
        $this->webDriverElementInspectorCallFactory = $webDriverElementInspectorCallFactory;
    }

    public static function createFactory(): VariableAssignmentCallFactory
    {
        return new VariableAssignmentCallFactory(
            AssertionCallFactory::createFactory(),
            ElementLocatorCallFactory::createFactory(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            TranspilationResultComposer::create(),
            ValueTranspiler::createTranspiler(),
            SingleQuotedStringEscaper::create(),
            WebDriverElementInspectorCallFactory::createFactory()
        );
    }

    public static function createElementLocatorPlaceholder(): VariablePlaceholder
    {
        return new VariablePlaceholder(self::DEFAULT_ELEMENT_LOCATOR_PLACEHOLDER_NAME);
    }

    public static function createCollectionPlaceholder(): VariablePlaceholder
    {
        return new VariablePlaceholder(self::DEFAULT_COLLECTION_PLACEHOLDER_NAME);
    }

    public static function createElementPlaceholder(): VariablePlaceholder
    {
        return new VariablePlaceholder(self::DEFAULT_ELEMENT_PLACEHOLDER_NAME);
    }

    public static function createAttributePlaceholder(): VariablePlaceholder
    {
        return new VariablePlaceholder(self::DEFAULT_ATTRIBUTE_PLACEHOLDER_NAME);
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     * @param VariablePlaceholder $elementLocatorPlaceholder
     * @param VariablePlaceholder $collectionPlaceholder
     * @return VariableAssignmentCall
     */
    public function createForElementCollection(
        DomIdentifierInterface $elementIdentifier,
        VariablePlaceholder $elementLocatorPlaceholder,
        VariablePlaceholder $collectionPlaceholder
    ) {
        $hasCall = $this->domCrawlerNavigatorCallFactory->createHasCallForTranspiledArguments(
            new TranspilationResult(
                [(string) $elementLocatorPlaceholder],
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            )
        );

        $findCall = $this->domCrawlerNavigatorCallFactory->createFindCallForTranspiledArguments(
            new TranspilationResult(
                [(string) $elementLocatorPlaceholder],
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            )
        );

        return $this->createForElementOrCollection(
            $elementIdentifier,
            $elementLocatorPlaceholder,
            $collectionPlaceholder,
            $hasCall,
            $findCall
        );
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     * @param VariablePlaceholder $elementLocatorPlaceholder
     * @param VariablePlaceholder $elementPlaceholder
     *
     * @return VariableAssignmentCall
     */
    public function createForElement(
        DomIdentifierInterface $elementIdentifier,
        VariablePlaceholder $elementLocatorPlaceholder,
        VariablePlaceholder $elementPlaceholder
    ) {
        $hasCall = $this->domCrawlerNavigatorCallFactory->createHasOneCallForTranspiledArguments(
            new TranspilationResult(
                [(string) $elementLocatorPlaceholder],
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            )
        );

        $findCall = $this->domCrawlerNavigatorCallFactory->createFindOneCallForTranspiledArguments(
            new TranspilationResult(
                [(string) $elementLocatorPlaceholder],
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            )
        );

        return $this->createForElementOrCollection(
            $elementIdentifier,
            $elementLocatorPlaceholder,
            $elementPlaceholder,
            $hasCall,
            $findCall
        );
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     * @param VariablePlaceholder $elementLocatorPlaceholder
     * @param VariablePlaceholder $elementPlaceholder
     *
     * @return VariableAssignmentCall
     */
    public function createForElementExistence(
        DomIdentifierInterface $elementIdentifier,
        VariablePlaceholder $elementLocatorPlaceholder,
        VariablePlaceholder $elementPlaceholder
    ): VariableAssignmentCall {
        $variablePlaceholders = new VariablePlaceholderCollection([
            $elementLocatorPlaceholder,
            $elementPlaceholder,
        ]);

        $hasCall = $this->domCrawlerNavigatorCallFactory->createHasCallForIdentifier($elementIdentifier);

        $assignmentStatement = $hasCall->extend(
            sprintf(
                '%s = %s',
                $elementPlaceholder,
                '%s'
            ),
            new UseStatementCollection(),
            $variablePlaceholders
        );

        return new VariableAssignmentCall($assignmentStatement, $elementPlaceholder);
    }

    /**
     * @param DomIdentifierInterface $attributeIdentifier
     * @param VariablePlaceholder $elementLocatorPlaceholder
     * @param VariablePlaceholder $elementPlaceholder
     * @param VariablePlaceholder $attributePlaceholder
     *
     * @return VariableAssignmentCall
     */
    public function createForAttribute(
        DomIdentifierInterface $attributeIdentifier,
        VariablePlaceholder $elementLocatorPlaceholder,
        VariablePlaceholder $elementPlaceholder,
        VariablePlaceholder $attributePlaceholder
    ): VariableAssignmentCall {
        $elementAssignmentCall = $this->createForElement(
            $attributeIdentifier,
            $elementLocatorPlaceholder,
            $elementPlaceholder
        );

        $variablePlaceholders = new VariablePlaceholderCollection();
        $variablePlaceholders = $variablePlaceholders->withAdditionalItems([
            $attributePlaceholder,
        ]);

        $elementPlaceholder = $elementAssignmentCall->getElementVariablePlaceholder();

        $attributeAssignmentStatement = $attributePlaceholder . ' = ' . sprintf(
            '%s->getAttribute(\'%s\')',
            $elementPlaceholder,
            $this->singleQuotedStringEscaper->escape((string) $attributeIdentifier->getAttributeName())
        );

        $statements = array_merge(
            $elementAssignmentCall->getLines(),
            [
                $attributeAssignmentStatement,
            ]
        );

        $calls = [
            $elementAssignmentCall,
        ];

        $transpilationResult = $this->transpilationResultComposer->compose(
            $statements,
            $calls,
            new UseStatementCollection(),
            $variablePlaceholders
        );

        return new VariableAssignmentCall($transpilationResult, $attributePlaceholder);
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     * @param VariablePlaceholder $valuePlaceholder
     *
     * @return VariableAssignmentCall
     */
    public function createForElementCollectionValue(
        DomIdentifierInterface $elementIdentifier,
        VariablePlaceholder $valuePlaceholder
    ): VariableAssignmentCall {
        $collectionCall = $this->createForElementCollection(
            $elementIdentifier,
            self::createElementLocatorPlaceholder(),
            $valuePlaceholder
        );
        $collectionPlaceholder = $collectionCall->getElementVariablePlaceholder();

        $variablePlaceholders = new VariablePlaceholderCollection();
        $variablePlaceholders = $variablePlaceholders->withAdditionalItems([
            $valuePlaceholder,
            $collectionPlaceholder,
        ]);

        $assignmentCall = $this->webDriverElementInspectorCallFactory->createGetValueCall($collectionPlaceholder);

        $assignmentStatement = $valuePlaceholder . ' = ' . $assignmentCall;

        $statements = array_merge(
            $collectionCall->getLines(),
            [
                $assignmentStatement,
            ]
        );

        $calls = [
            $collectionCall,
            $assignmentCall,
        ];

        $transpilationResult = $this->transpilationResultComposer->compose(
            $statements,
            $calls,
            new UseStatementCollection(),
            $variablePlaceholders
        );

        return new VariableAssignmentCall($transpilationResult, $valuePlaceholder);
    }

    /**
     * @param DomIdentifierInterface $attributeIdentifier
     * @param VariablePlaceholder $valuePlaceholder
     *
     * @return VariableAssignmentCall
     */
    public function createForAttributeValue(
        DomIdentifierInterface $attributeIdentifier,
        VariablePlaceholder $valuePlaceholder
    ): VariableAssignmentCall {
        $assignmentCall = $this->createForAttribute(
            $attributeIdentifier,
            self::createElementLocatorPlaceholder(),
            self::createElementPlaceholder(),
            $valuePlaceholder
        );

        $variablePlaceholders = new VariablePlaceholderCollection();
        $variablePlaceholders = $variablePlaceholders->withAdditionalItems([
            $valuePlaceholder
        ]);

        $transpilationResult = $this->transpilationResultComposer->compose(
            $assignmentCall->getLines(),
            [$assignmentCall],
            new UseStatementCollection(),
            $variablePlaceholders
        );

        return new VariableAssignmentCall($transpilationResult, $valuePlaceholder);
    }

    /**
     * @param DomIdentifierInterface $attributeIdentifier
     * @param VariablePlaceholder $valuePlaceholder
     *
     * @return VariableAssignmentCall
     */
    public function createForAttributeExistence(
        DomIdentifierInterface $attributeIdentifier,
        VariablePlaceholder $valuePlaceholder
    ): VariableAssignmentCall {
        $variablePlaceholders = new VariablePlaceholderCollection();
        $variablePlaceholders = $variablePlaceholders->withAdditionalItems([
            $valuePlaceholder
        ]);

        $assignmentCall = $this->createForAttribute(
            $attributeIdentifier,
            self::createElementLocatorPlaceholder(),
            self::createElementPlaceholder(),
            $valuePlaceholder
        );

        $assignmentCall = $assignmentCall->extend(
            '%s !== null',
            new UseStatementCollection(),
            $variablePlaceholders
        );

        $transpilationResult = $this->transpilationResultComposer->compose(
            $assignmentCall->getLines(),
            [$assignmentCall],
            new UseStatementCollection(),
            new VariablePlaceholderCollection()
        );

        return new VariableAssignmentCall($transpilationResult, $valuePlaceholder);
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     * @param VariablePlaceholder $elementLocatorPlaceholder
     * @param VariablePlaceholder $returnValuePlaceholder
     * @param TranspilationResultInterface $hasCall
     * @param TranspilationResultInterface $findCall
     *
     * @return VariableAssignmentCall
     */
    private function createForElementOrCollection(
        DomIdentifierInterface $elementIdentifier,
        VariablePlaceholder $elementLocatorPlaceholder,
        VariablePlaceholder $returnValuePlaceholder,
        TranspilationResultInterface $hasCall,
        TranspilationResultInterface $findCall
    ) {
        $variablePlaceholders = new VariablePlaceholderCollection();
        $variablePlaceholders = $variablePlaceholders->withAdditionalItems([
            $elementLocatorPlaceholder,
            $returnValuePlaceholder,
        ]);

        $hasVariablePlaceholder = $variablePlaceholders->create('HAS');

        $elementLocatorConstructor = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $hasVariableAssignmentCall = new VariableAssignmentCall(
            $hasCall->extend(
                $hasVariablePlaceholder . ' = ' . $hasCall,
                new UseStatementCollection(),
                $variablePlaceholders
            ),
            $hasVariablePlaceholder
        );

        $elementExistsAssertionCall = $this->assertionCallFactory->createValueIsTrueAssertionCall(
            $hasVariableAssignmentCall
        );

        $elementLocatorConstructorStatement = $elementLocatorPlaceholder . ' = ' . $elementLocatorConstructor;
        $findStatement = $returnValuePlaceholder . ' = ' . $findCall;

        $statements = array_merge(
            [
                $elementLocatorConstructorStatement,
            ],
            $elementExistsAssertionCall->getLines(),
            [
                $findStatement,
            ]
        );

        $calls = [
            $elementLocatorConstructor,
            $hasCall,
            $findCall,
            $elementExistsAssertionCall,
        ];

        $transpilationResult = $this->transpilationResultComposer->compose(
            $statements,
            $calls,
            new UseStatementCollection(),
            $variablePlaceholders
        );

        return new VariableAssignmentCall($transpilationResult, $returnValuePlaceholder);
    }

    /**
     * @param ValueInterface $value
     * @param VariablePlaceholder $variablePlaceholder
     *
     * @return VariableAssignmentCall
     *
     * @throws NonTranspilableModelException
     */
    public function createForScalar(ValueInterface $value, VariablePlaceholder $variablePlaceholder)
    {
        $variablePlaceholders = new VariablePlaceholderCollection([
            $variablePlaceholder,
        ]);

        $variableAccessCall = $this->valueTranspiler->transpile($value);
        $variableAccessLines = $variableAccessCall->getLines();
        $variableAccessLastLine = array_pop($variableAccessLines);

        $assignmentStatement = sprintf(
            '%s = %s ?? null',
            $variablePlaceholder,
            $variableAccessLastLine
        );

        $statements = array_merge($variableAccessLines, [
            $assignmentStatement,
        ]);

        $calls = [
            $variableAccessCall,
        ];

        $transpilationResult = $this->transpilationResultComposer->compose(
            $statements,
            $calls,
            new UseStatementCollection(),
            $variablePlaceholders
        );

        return new VariableAssignmentCall($transpilationResult, $variablePlaceholder);
    }

    /**
     * @param ValueInterface $value
     * @param VariablePlaceholder $variablePlaceholder
     *
     * @return VariableAssignmentCall
     *
     * @throws NonTranspilableModelException
     */
    public function createForScalarExistence(ValueInterface $value, VariablePlaceholder $variablePlaceholder)
    {
        $variablePlaceholders = new VariablePlaceholderCollection([
            $variablePlaceholder,
        ]);

        $assignmentCall = $this->createForScalar($value, $variablePlaceholder);
        $assignmentCall = $assignmentCall->extend(
            '%s',
            new UseStatementCollection(),
            $variablePlaceholders
        );

        $comparisonStatement = $variablePlaceholder . ' = ' . $variablePlaceholder . ' !== null';

        $transpilationResult = $this->transpilationResultComposer->compose(
            array_merge(
                $assignmentCall->getLines(),
                [
                    $comparisonStatement,
                ]
            ),
            [
                $assignmentCall,
            ],
            new UseStatementCollection(),
            $variablePlaceholders
        );

        return new VariableAssignmentCall($transpilationResult, $variablePlaceholder);
    }

    /**
     * @param ValueInterface $value
     * @param VariablePlaceholder $placeholder
     *
     * @return VariableAssignmentCall|null
     *
     * @throws NonTranspilableModelException
     */
    public function createValueVariableAssignmentCall(
        ValueInterface $value,
        VariablePlaceholder $placeholder
    ): ?VariableAssignmentCall {
        $isScalarValue =
            $value instanceof LiteralValueInterface ||
            ($value instanceof ObjectValueInterface && ObjectValueType::ENVIRONMENT_PARAMETER === $value->getType()) ||
            ($value instanceof ObjectValueInterface && ObjectValueType::BROWSER_PROPERTY === $value->getType()) ||
            ($value instanceof ObjectValueInterface && ObjectValueType::PAGE_PROPERTY === $value->getType());

        if ($isScalarValue) {
            return $this->createForScalar(
                $value,
                $placeholder
            );
        }

        if ($value instanceof DomIdentifierValueInterface) {
            $identifier = $value->getIdentifier();

            return null === $identifier->getAttributeName()
                ? $this->createForElementCollectionValue($identifier, $placeholder)
                : $this->createForAttributeValue($identifier, $placeholder);
        }

        return null;
    }

    /**
     * @param ValueInterface $value
     * @param VariablePlaceholder $placeholder
     *
     * @return VariableAssignmentCall|null
     *
     * @throws NonTranspilableModelException
     */
    public function createValueExistenceAssignmentCall(
        ValueInterface $value,
        VariablePlaceholder $placeholder
    ): ?VariableAssignmentCall {
        $isScalarValue =
            ($value instanceof ObjectValueInterface && ObjectValueType::ENVIRONMENT_PARAMETER === $value->getType()) ||
            ($value instanceof ObjectValueInterface && ObjectValueType::BROWSER_PROPERTY === $value->getType()) ||
            ($value instanceof ObjectValueInterface && ObjectValueType::PAGE_PROPERTY === $value->getType());

        if ($isScalarValue) {
            return $this->createForScalarExistence(
                $value,
                $placeholder
            );
        }

        if ($value instanceof DomIdentifierValueInterface) {
            $identifier = $value->getIdentifier();

            return null === $identifier->getAttributeName()
                ? $this->createForElementExistence(
                    $identifier,
                    VariableAssignmentCallFactory::createElementLocatorPlaceholder(),
                    $placeholder
                )
                : $this->createForAttributeExistence($identifier, $placeholder);
        }

        return null;
    }
}
