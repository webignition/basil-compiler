<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilationResultComposer;
use webignition\BasilTranspiler\Value\ValueTranspiler;

class VariableAssignmentCallFactory
{
    const DEFAULT_ELEMENT_LOCATOR_PLACEHOLDER_NAME = 'ELEMENT_LOCATOR';
    const DEFAULT_ELEMENT_PLACEHOLDER_NAME = 'ELEMENT';
    const DEFAULT_COLLECTION_PLACEHOLDER_NAME = 'COLLECTION';

    private $assertionCallFactory;
    private $elementLocatorCallFactory;
    private $domCrawlerNavigatorCallFactory;
    private $transpilationResultComposer;
    private $valueTranspiler;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        ElementLocatorCallFactory $elementLocatorCallFactory,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        TranspilationResultComposer $transpilationResultComposer,
        ValueTranspiler $valueTranspiler
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->transpilationResultComposer = $transpilationResultComposer;
        $this->valueTranspiler = $valueTranspiler;
    }

    public static function createFactory(): VariableAssignmentCallFactory
    {
        return new VariableAssignmentCallFactory(
            AssertionCallFactory::createFactory(),
            ElementLocatorCallFactory::createFactory(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            TranspilationResultComposer::create(),
            ValueTranspiler::createTranspiler()
        );
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     * @param string $elementLocatorPlaceholderName
     * @param string $elementPlaceholderName
     * @param string $collectionPlaceholderName
     *
     * @return VariableAssignmentCall
     *
     * @throws NonTranspilableModelException
     */
    public function createForElement(
        ElementIdentifierInterface $elementIdentifier,
        string $elementLocatorPlaceholderName = self::DEFAULT_ELEMENT_LOCATOR_PLACEHOLDER_NAME,
        string $elementPlaceholderName = self::DEFAULT_ELEMENT_PLACEHOLDER_NAME,
        string $collectionPlaceholderName = self::DEFAULT_COLLECTION_PLACEHOLDER_NAME
    ) {
        $variablePlaceholders = new VariablePlaceholderCollection();

        $elementLocatorPlaceholder = $variablePlaceholders->create($elementLocatorPlaceholderName);
        $elementPlaceholder = $variablePlaceholders->create($elementPlaceholderName);
        $collectionPlaceholder = $variablePlaceholders->create($collectionPlaceholderName);

        $elementLocatorConstructor = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $hasElementCall = $this->domCrawlerNavigatorCallFactory->createHasCallForTranspiledArguments(
            new TranspilationResult(
                [(string) $elementLocatorPlaceholder],
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            )
        );

        $findElementCall = $this->domCrawlerNavigatorCallFactory->createFindCallForTranspiledArguments(
            new TranspilationResult(
                [(string) $elementLocatorPlaceholder],
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            )
        );

        $elementExistsAssertionCall = $this->assertionCallFactory->createElementExistsAssertionCall($hasElementCall);

        $elementLocatorConstructorStatement = $elementLocatorPlaceholder . ' = ' . $elementLocatorConstructor;
        $elementExistsStatement = (string) $elementExistsAssertionCall;

        $collectionFindStatement = $collectionPlaceholder . ' = ' . $findElementCall;

        $elementFindStatement = $elementPlaceholder . ' = ' . $collectionPlaceholder . '->get(0)';

        $statements = [
            $elementLocatorConstructorStatement,
            $elementExistsStatement,
            $collectionFindStatement,
            $elementFindStatement,
        ];

        $calls = [
            $elementLocatorConstructor,
            $hasElementCall,
            $findElementCall,
            $elementExistsAssertionCall,
        ];

        $transpilationResult = $this->transpilationResultComposer->compose(
            $statements,
            $calls,
            new UseStatementCollection(),
            $variablePlaceholders
        );

        return new VariableAssignmentCall($transpilationResult, $elementPlaceholder);
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
        $variableAssignmentCall = $variableAccessCall->extend(
            sprintf(
                '%s = %s ?? null',
                (string) $variablePlaceholder,
                '%s'
            ),
            new UseStatementCollection(),
            $variablePlaceholders
        );

        return new VariableAssignmentCall($variableAssignmentCall, $variablePlaceholder);
    }
}
