<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\ObjectValueTypeExaminer;
use webignition\BasilTranspiler\SingleQuotedStringEscaper;
use webignition\BasilTranspiler\Value\ValueTranspiler;

class VariableAssignmentCallFactory
{
    const DEFAULT_ELEMENT_LOCATOR_PLACEHOLDER_NAME = 'ELEMENT_LOCATOR';
    const DEFAULT_ELEMENT_PLACEHOLDER_NAME = 'ELEMENT';

    private $assertionCallFactory;
    private $elementLocatorCallFactory;
    private $domCrawlerNavigatorCallFactory;
    private $valueTranspiler;
    private $singleQuotedStringEscaper;
    private $webDriverElementInspectorCallFactory;
    private $objectValueTypeExaminer;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        ElementLocatorCallFactory $elementLocatorCallFactory,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        ValueTranspiler $valueTranspiler,
        SingleQuotedStringEscaper $singleQuotedStringEscaper,
        WebDriverElementInspectorCallFactory $webDriverElementInspectorCallFactory,
        ObjectValueTypeExaminer $objectValueTypeExaminer
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->valueTranspiler = $valueTranspiler;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
        $this->webDriverElementInspectorCallFactory = $webDriverElementInspectorCallFactory;
        $this->objectValueTypeExaminer = $objectValueTypeExaminer;
    }

    public static function createFactory(): VariableAssignmentCallFactory
    {
        return new VariableAssignmentCallFactory(
            AssertionCallFactory::createFactory(),
            ElementLocatorCallFactory::createFactory(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            ValueTranspiler::createTranspiler(),
            SingleQuotedStringEscaper::create(),
            WebDriverElementInspectorCallFactory::createFactory(),
            ObjectValueTypeExaminer::createExaminer()
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
        $arguments = new CompilableSource([(string) $elementLocatorPlaceholder]);
        $argumentsMetadata = $arguments->getCompilationMetadata();
        $argumentsMetadata = $argumentsMetadata->withVariableExports(new VariablePlaceholderCollection([
            $elementLocatorPlaceholder,
        ]));

        $arguments = $arguments->mergeCompilationData([
            $argumentsMetadata,
        ]);

        $hasCall = $this->domCrawlerNavigatorCallFactory->createHasOneCallForTranspiledArguments($arguments);
        $findCall = $this->domCrawlerNavigatorCallFactory->createFindOneCallForTranspiledArguments($arguments);

        return $this->createForElementOrCollection(
            $elementIdentifier,
            $elementLocatorPlaceholder,
            $elementPlaceholder,
            $hasCall,
            $findCall
        );
    }

    /**
     * @param ValueInterface $value
     * @param VariablePlaceholder $placeholder
     * @param string $type
     * @param string $default
     *
     * @return VariableAssignmentCall|null
     *
     * @throws NonTranspilableModelException
     */
    public function createForValue(
        ValueInterface $value,
        VariablePlaceholder $placeholder,
        string $type = 'string',
        string $default = 'null'
    ): ?VariableAssignmentCall {
        $assignment = null;

        $isOfScalarObjectType = $this->objectValueTypeExaminer->isOfType($value, [
            ObjectValueType::BROWSER_PROPERTY,
            ObjectValueType::ENVIRONMENT_PARAMETER,
            ObjectValueType::PAGE_PROPERTY,
        ]);

        $isScalarValue = $value instanceof LiteralValueInterface || $isOfScalarObjectType;

        if ($isScalarValue) {
            $assignment = $this->createForScalar($value, $placeholder, $default);
        }

        if (null === $assignment && $value instanceof DomIdentifierValueInterface) {
            $identifier = $value->getIdentifier();

            if (null === $identifier->getAttributeName()) {
                $assignment = $this->createForElementCollectionValue($identifier, $placeholder);
            } else {
                $assignment = $this->createForAttribute(
                    $identifier,
                    $this->createElementLocatorPlaceholder(),
                    $this->createElementPlaceholder(),
                    $placeholder
                );
            }
        }

        if ($assignment instanceof VariableAssignmentCall) {
            $variableAssignmentCallPlaceholder = $assignment->getVariablePlaceholder();

            $assignment = new VariableAssignmentCall(
                new CompilableSource(
                    array_merge(
                        $assignment->getStatements(),
                        [
                            sprintf('(%s) %s', $type, (string) $variableAssignmentCallPlaceholder)
                        ]
                    ),
                    $assignment->getCompilationMetadata()
                ),
                $variableAssignmentCallPlaceholder
            );
        }

        return $assignment;
    }

    /**
     * @param ValueInterface $value
     * @param VariablePlaceholder $placeholder
     *
     * @return VariableAssignmentCall|null
     *
     * @throws NonTranspilableModelException
     */
    public function createForValueExistence(
        ValueInterface $value,
        VariablePlaceholder $placeholder
    ): ?VariableAssignmentCall {
        $isScalarValue = $this->objectValueTypeExaminer->isOfType($value, [
            ObjectValueType::BROWSER_PROPERTY,
            ObjectValueType::ENVIRONMENT_PARAMETER,
            ObjectValueType::PAGE_PROPERTY,
        ]);

        if ($isScalarValue) {
            return $this->createForScalarExistence(
                $value,
                $placeholder
            );
        }

        if ($value instanceof DomIdentifierValueInterface) {
            $identifier = $value->getIdentifier();

            return null === $identifier->getAttributeName()
                ? $this->createForElementExistence($identifier, $placeholder)
                : $this->createForAttributeExistence($identifier, $placeholder);
        }

        return null;
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     * @param VariablePlaceholder $elementLocatorPlaceholder
     * @param VariablePlaceholder $collectionPlaceholder
     *
     * @return VariableAssignmentCall
     */
    public function createForElementCollection(
        DomIdentifierInterface $elementIdentifier,
        VariablePlaceholder $elementLocatorPlaceholder,
        VariablePlaceholder $collectionPlaceholder
    ) {
        $arguments = new CompilableSource([(string) $elementLocatorPlaceholder]);

        $arguments = $arguments->mergeCompilationData([
            (new CompilationMetadata())->withVariableExports(new VariablePlaceholderCollection([
                $elementLocatorPlaceholder,
            ]))
        ]);

        $hasCall = $this->domCrawlerNavigatorCallFactory->createHasCallForTranspiledArguments($arguments);
        $findCall = $this->domCrawlerNavigatorCallFactory->createFindCallForTranspiledArguments($arguments);

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
     * @param VariablePlaceholder $elementPlaceholder
     *
     * @return VariableAssignmentCall
     */
    private function createForElementExistence(
        DomIdentifierInterface $elementIdentifier,
        VariablePlaceholder $elementPlaceholder
    ): VariableAssignmentCall {
        $hasCall = $this->domCrawlerNavigatorCallFactory->createHasCallForIdentifier($elementIdentifier);

        $variableExports = new VariablePlaceholderCollection([
            $elementPlaceholder,
        ]);

        $compilationMetadata = (new CompilationMetadata())
            ->merge([$hasCall->getCompilationMetadata()])
            ->withAdditionalVariableExports($variableExports);

        $elementExistenceAccess = new CompilableSource(
            [
                (string) $hasCall,
            ],
            $compilationMetadata
        );

        return new VariableAssignmentCall(
            $elementExistenceAccess,
            $elementPlaceholder
        );
    }

    /**
     * @param DomIdentifierInterface $attributeIdentifier
     * @param VariablePlaceholder $elementLocatorPlaceholder
     * @param VariablePlaceholder $elementPlaceholder
     * @param VariablePlaceholder $attributePlaceholder
     *
     * @return VariableAssignmentCall
     */
    private function createForAttribute(
        DomIdentifierInterface $attributeIdentifier,
        VariablePlaceholder $elementLocatorPlaceholder,
        VariablePlaceholder $elementPlaceholder,
        VariablePlaceholder $attributePlaceholder
    ): VariableAssignmentCall {
        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->withAdditionalItems([
            $attributePlaceholder,
        ]);

        $elementAssignment = $this->createForElement(
            $attributeIdentifier,
            $elementLocatorPlaceholder,
            $elementPlaceholder
        );

        $elementPlaceholder = $elementAssignment->getVariablePlaceholder();

        $compilationMetadata = (new CompilationMetadata())
            ->merge([$elementAssignment->getCompilationMetadata()])
            ->withAdditionalVariableExports($variableExports);

        return new VariableAssignmentCall(
            new CompilableSource(
                array_merge(
                    $elementAssignment->getStatements(),
                    [
                        sprintf(
                            '%s->getAttribute(\'%s\')',
                            $elementPlaceholder,
                            $this->singleQuotedStringEscaper->escape((string) $attributeIdentifier->getAttributeName())
                        ),
                    ]
                ),
                $compilationMetadata
            ),
            $attributePlaceholder
        );
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     * @param VariablePlaceholder $valuePlaceholder
     *
     * @return VariableAssignmentCall
     */
    private function createForElementCollectionValue(
        DomIdentifierInterface $elementIdentifier,
        VariablePlaceholder $valuePlaceholder
    ): VariableAssignmentCall {
        $collectionAssignment = $this->createForElementCollection(
            $elementIdentifier,
            $this->createElementLocatorPlaceholder(),
            $valuePlaceholder
        );

        $collectionPlaceholder = $collectionAssignment->getVariablePlaceholder();

        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->withAdditionalItems([
            $valuePlaceholder,
            $collectionPlaceholder,
        ]);

        $getValueCall = $this->webDriverElementInspectorCallFactory->createGetValueCall($collectionPlaceholder);

        $compilationMetadata = (new CompilationMetadata())->withVariableExports($variableExports);
        $compilationMetadata = $compilationMetadata->merge([
            $collectionAssignment->getCompilationMetadata(),
            $getValueCall->getCompilationMetadata(),
        ]);

        $valueAssignmentSource = new CompilableSource(
            array_merge(
                $collectionAssignment->getStatements(),
                $getValueCall->getStatements()
            ),
            $compilationMetadata
        );

        return new VariableAssignmentCall($valueAssignmentSource, $valuePlaceholder);
    }

    /**
     * @param DomIdentifierInterface $attributeIdentifier
     * @param VariablePlaceholder $valuePlaceholder
     *
     * @return VariableAssignmentCall
     */
    private function createForAttributeExistence(
        DomIdentifierInterface $attributeIdentifier,
        VariablePlaceholder $valuePlaceholder
    ): VariableAssignmentCall {
        $assignment = $this->createForAttribute(
            $attributeIdentifier,
            $this->createElementLocatorPlaceholder(),
            $this->createElementPlaceholder(),
            $valuePlaceholder
        );

        $existenceSource = new CompilableSource(
            array_merge(
                $assignment->getStatements(),
                [
                    $valuePlaceholder . ' !== null'
                ]
            ),
            $assignment->getCompilationMetadata()
        );

        return new VariableAssignmentCall($existenceSource, $valuePlaceholder);
    }


    /**
     * @param DomIdentifierInterface $elementIdentifier
     * @param VariablePlaceholder $elementLocatorPlaceholder
     * @param VariablePlaceholder $returnValuePlaceholder
     * @param CompilableSourceInterface $hasCall
     * @param CompilableSourceInterface $findCall
     *
     * @return VariableAssignmentCall
     */
    private function createForElementOrCollection(
        DomIdentifierInterface $elementIdentifier,
        VariablePlaceholder $elementLocatorPlaceholder,
        VariablePlaceholder $returnValuePlaceholder,
        CompilableSourceInterface $hasCall,
        CompilableSourceInterface $findCall
    ) {
        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->withAdditionalItems([
            $elementLocatorPlaceholder,
            $returnValuePlaceholder,
        ]);

        $hasVariablePlaceholder = $variableExports->create('HAS');

        $elementLocatorConstructor = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);
        $elementLocatorConstructorStatement = $elementLocatorPlaceholder . ' = ' . $elementLocatorConstructor;

        $hasAssignment = new VariableAssignmentCall(
            new CompilableSource(
                [
                    (string) $hasCall,
                ],
                $hasCall->getCompilationMetadata()
            ),
            $hasVariablePlaceholder
        );

        $elementExistsAssertion = $this->assertionCallFactory->createValueIsTrueAssertionCall($hasAssignment);

        $compilationMetadata = (new CompilationMetadata())->merge([
            $hasCall->getCompilationMetadata(),
            $findCall->getCompilationMetadata(),
            $elementLocatorConstructor->getCompilationMetadata(),
            $elementExistsAssertion->getCompilationMetadata(),
        ]);

        $compilationMetadata = $compilationMetadata->withAdditionalVariableExports($variableExports);

        $compilableSource = new CompilableSource(
            array_merge(
                [
                    $elementLocatorConstructorStatement,
                ],
                $elementExistsAssertion->getStatements(),
                [
                    (string) $findCall,
                ]
            ),
            $compilationMetadata
        );

        return new VariableAssignmentCall($compilableSource, $returnValuePlaceholder);
    }

    /**
     * @param ValueInterface $value
     * @param VariablePlaceholder $variablePlaceholder
     * @param string $default
     *
     * @return VariableAssignmentCall
     *
     * @throws NonTranspilableModelException
     */
    private function createForScalar(
        ValueInterface $value,
        VariablePlaceholder $variablePlaceholder,
        string $default = 'null'
    ): VariableAssignmentCall {
        $variableExports = new VariablePlaceholderCollection([
            $variablePlaceholder,
        ]);

        $accessCall = $this->valueTranspiler->transpile($value);
        $accessStatements = $accessCall->getStatements();
        $variableAccessLastStatement = array_pop($accessStatements);

        $compilationMetadata = (new CompilationMetadata())->withVariableExports($variableExports);
        $compilationMetadata = $compilationMetadata->merge([
            $accessCall->getCompilationMetadata(),
        ]);

        $source = new CompilableSource(
            array_merge(
                $accessStatements,
                [
                    $variableAccessLastStatement . ' ?? ' . $default,
                ]
            ),
            $compilationMetadata
        );

        return new VariableAssignmentCall($source, $variablePlaceholder);
    }

    /**
     * @param ValueInterface $value
     * @param VariablePlaceholder $variablePlaceholder
     *
     * @return VariableAssignmentCall
     *
     * @throws NonTranspilableModelException
     */
    private function createForScalarExistence(ValueInterface $value, VariablePlaceholder $variablePlaceholder)
    {
        $variableExports = new VariablePlaceholderCollection([
            $variablePlaceholder,
        ]);

        $assignment = $this->createForScalar($value, $variablePlaceholder);

        $compilationMetadata = (new CompilationMetadata())
            ->merge([$assignment->getCompilationMetadata()])
            ->withAdditionalVariableExports($variableExports);

        $comparisonSource = new CompilableSource(
            array_merge(
                $assignment->getStatements(),
                [
                    $variablePlaceholder . ' !== null'
                ]
            ),
            $compilationMetadata
        );

        return new VariableAssignmentCall(
            $comparisonSource,
            $variablePlaceholder
        );
    }

    private function createElementLocatorPlaceholder(): VariablePlaceholder
    {
        return new VariablePlaceholder(self::DEFAULT_ELEMENT_LOCATOR_PLACEHOLDER_NAME);
    }

    private function createElementPlaceholder(): VariablePlaceholder
    {
        return new VariablePlaceholder(self::DEFAULT_ELEMENT_PLACEHOLDER_NAME);
    }
}
