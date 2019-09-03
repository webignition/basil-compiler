<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilModel\Identifier\AttributeIdentifierInterface;
use webignition\BasilModel\Identifier\ElementIdentifierInterface;
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

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        ElementLocatorCallFactory $elementLocatorCallFactory,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        TranspilationResultComposer $transpilationResultComposer,
        ValueTranspiler $valueTranspiler,
        SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->transpilationResultComposer = $transpilationResultComposer;
        $this->valueTranspiler = $valueTranspiler;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): VariableAssignmentCallFactory
    {
        return new VariableAssignmentCallFactory(
            AssertionCallFactory::createFactory(),
            ElementLocatorCallFactory::createFactory(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            TranspilationResultComposer::create(),
            ValueTranspiler::createTranspiler(),
            SingleQuotedStringEscaper::create()
        );
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     * @param string $elementLocatorPlaceholderName
     * @param string $collectionPlaceholderName
     *
     * @return VariableAssignmentCall
     *
     * @throws NonTranspilableModelException
     */
    public function createForElementCollection(
        ElementIdentifierInterface $elementIdentifier,
        string $elementLocatorPlaceholderName = self::DEFAULT_ELEMENT_LOCATOR_PLACEHOLDER_NAME,
        string $collectionPlaceholderName = self::DEFAULT_COLLECTION_PLACEHOLDER_NAME
    ) {
        $variablePlaceholders = new VariablePlaceholderCollection();

        $elementLocatorPlaceholder = $variablePlaceholders->create($elementLocatorPlaceholderName);
        $returnValuePlaceholder = $variablePlaceholders->create($collectionPlaceholderName);

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
            $returnValuePlaceholder,
            $hasCall,
            $findCall
        );
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     * @param string $elementLocatorPlaceholderName
     * @param string $elementPlaceholderName
     *
     * @return VariableAssignmentCall
     *
     * @throws NonTranspilableModelException
     */
    public function createForElement(
        ElementIdentifierInterface $elementIdentifier,
        string $elementLocatorPlaceholderName = self::DEFAULT_ELEMENT_LOCATOR_PLACEHOLDER_NAME,
        string $elementPlaceholderName = self::DEFAULT_ELEMENT_PLACEHOLDER_NAME
    ) {
        $variablePlaceholders = new VariablePlaceholderCollection();

        $elementLocatorPlaceholder = $variablePlaceholders->create($elementLocatorPlaceholderName);
        $returnValuePlaceholder = $variablePlaceholders->create($elementPlaceholderName);

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
            $returnValuePlaceholder,
            $hasCall,
            $findCall
        );
    }

    /**
     * @param AttributeIdentifierInterface $attributeIdentifier
     * @param string $elementLocatorPlaceholderName
     * @param string $elementPlaceholderName
     * @param string $attributePlaceholderName
     *
     * @return VariableAssignmentCall
     *
     * @throws NonTranspilableModelException
     */
    public function createForAttribute(
        AttributeIdentifierInterface $attributeIdentifier,
        string $elementLocatorPlaceholderName = self::DEFAULT_ELEMENT_LOCATOR_PLACEHOLDER_NAME,
        string $elementPlaceholderName = self::DEFAULT_ELEMENT_PLACEHOLDER_NAME,
        string $attributePlaceholderName = self::DEFAULT_ATTRIBUTE_PLACEHOLDER_NAME
    ): VariableAssignmentCall {
        $variablePlaceholders = new VariablePlaceholderCollection();
        $attributePlaceholder = $variablePlaceholders->create($attributePlaceholderName);

        $elementAssignmentCall = $this->createForElement(
            $attributeIdentifier->getElementIdentifier(),
            $elementLocatorPlaceholderName,
            $elementPlaceholderName
        );

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
     * @param ElementIdentifierInterface $elementIdentifier
     * @param VariablePlaceholder $elementLocatorPlaceholder
     * @param VariablePlaceholder $returnValuePlaceholder
     * @param TranspilationResultInterface $hasCall
     * @param TranspilationResultInterface $findCall
     *
     * @return VariableAssignmentCall
     *
     * @throws NonTranspilableModelException
     */
    private function createForElementOrCollection(
        ElementIdentifierInterface $elementIdentifier,
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
}
