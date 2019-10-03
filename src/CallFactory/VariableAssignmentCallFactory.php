<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
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
        $arguments = $arguments->withVariableExports(new VariablePlaceholderCollection([
            $elementLocatorPlaceholder,
        ]));

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
        $assignmentCall = null;

        $isOfScalarObjectType = $this->objectValueTypeExaminer->isOfType($value, [
            ObjectValueType::BROWSER_PROPERTY,
            ObjectValueType::ENVIRONMENT_PARAMETER,
            ObjectValueType::PAGE_PROPERTY,
        ]);

        $isScalarValue = $value instanceof LiteralValueInterface || $isOfScalarObjectType;

        if ($isScalarValue) {
            $assignmentCall = $this->createForScalar($value, $placeholder, $default);
        }

        if (null === $assignmentCall && $value instanceof DomIdentifierValueInterface) {
            $identifier = $value->getIdentifier();

            $assignmentCall = null === $identifier->getAttributeName()
                ? $this->createForElementCollectionValue($identifier, $placeholder)
                : $this->createForAttributeValue($identifier, $placeholder);
        }

        if ($assignmentCall instanceof VariableAssignmentCall) {
            $variableAssignmentCallPlaceholder = $assignmentCall->getElementVariablePlaceholder();

            $typeCastStatement = sprintf(
                '%s = (%s) %s',
                (string) $variableAssignmentCallPlaceholder,
                $type,
                (string) $variableAssignmentCallPlaceholder
            );

            $compilableSource = new CompilableSource(array_merge(
                $assignmentCall->getStatements(),
                [$typeCastStatement]
            ));

            $compilableSource = $compilableSource->withClassDependencies($assignmentCall->getClassDependencies());
            $compilableSource = $compilableSource->withVariableDependencies($assignmentCall->getVariableDependencies());
            $compilableSource = $compilableSource->withVariableExports($assignmentCall->getVariableExports());

            $assignmentCall = new VariableAssignmentCall(
                $compilableSource,
                $assignmentCall->getElementVariablePlaceholder()
            );
        }

        return $assignmentCall;
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
                ? $this->createForElementExistence(
                    $identifier,
                    VariableAssignmentCallFactory::createElementLocatorPlaceholder(),
                    $placeholder
                )
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
        $arguments = $arguments->withVariableExports(new VariablePlaceholderCollection([
            $elementLocatorPlaceholder,
        ]));

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
     * @param VariablePlaceholder $elementLocatorPlaceholder
     * @param VariablePlaceholder $elementPlaceholder
     *
     * @return VariableAssignmentCall
     */
    private function createForElementExistence(
        DomIdentifierInterface $elementIdentifier,
        VariablePlaceholder $elementLocatorPlaceholder,
        VariablePlaceholder $elementPlaceholder
    ): VariableAssignmentCall {
        $hasCall = $this->domCrawlerNavigatorCallFactory->createHasCallForIdentifier($elementIdentifier);

        $classDependencies = new ClassDependencyCollection();
        $classDependencies = $classDependencies->merge([$hasCall->getClassDependencies()]);

        $variableDependencies = new VariablePlaceholderCollection();
        $variableDependencies = $variableDependencies->merge([$hasCall->getVariableDependencies()]);

        $variableExports = new VariablePlaceholderCollection([
            $elementLocatorPlaceholder,
            $elementPlaceholder,
        ]);

        $variableExports = $variableExports->merge([$hasCall->getVariableExports()]);

        $assignmentStatement = sprintf(
            '%s = %s',
            (string) $elementPlaceholder,
            (string) $hasCall
        );

        $compilableSource = new CompilableSource([$assignmentStatement]);
        $compilableSource = $compilableSource->withClassDependencies($classDependencies);
        $compilableSource = $compilableSource->withVariableDependencies($variableDependencies);
        $compilableSource = $compilableSource->withVariableExports($variableExports);

        return new VariableAssignmentCall($compilableSource, $elementPlaceholder);
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
        $classDependencies = new ClassDependencyCollection();
        $variableDependencies = new VariablePlaceholderCollection();

        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->withAdditionalItems([
            $attributePlaceholder,
        ]);

        $elementAssignmentCall = $this->createForElement(
            $attributeIdentifier,
            $elementLocatorPlaceholder,
            $elementPlaceholder
        );

        $elementPlaceholder = $elementAssignmentCall->getElementVariablePlaceholder();

        $attributeAssignmentStatement = $attributePlaceholder . ' = ' . sprintf(
            '%s->getAttribute(\'%s\')',
            $elementPlaceholder,
            $this->singleQuotedStringEscaper->escape((string) $attributeIdentifier->getAttributeName())
        );

        $classDependencies = $classDependencies->merge([$elementAssignmentCall->getClassDependencies()]);
        $variableDependencies = $variableDependencies->merge([$elementAssignmentCall->getVariableDependencies()]);
        $variableExports = $variableExports->merge([$elementAssignmentCall->getVariableExports()]);

        $statements = array_merge(
            $elementAssignmentCall->getStatements(),
            [
                $attributeAssignmentStatement,
            ]
        );

        $compilableSource = new CompilableSource($statements);
        $compilableSource = $compilableSource->withClassDependencies($classDependencies);
        $compilableSource = $compilableSource->withVariableDependencies($variableDependencies);
        $compilableSource = $compilableSource->withVariableExports($variableExports);

        return new VariableAssignmentCall($compilableSource, $attributePlaceholder);
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
        $collectionCall = $this->createForElementCollection(
            $elementIdentifier,
            $this->createElementLocatorPlaceholder(),
            $valuePlaceholder
        );

        $collectionPlaceholder = $collectionCall->getElementVariablePlaceholder();

        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->withAdditionalItems([
            $valuePlaceholder,
            $collectionPlaceholder,
        ]);

        $assignmentCall = $this->webDriverElementInspectorCallFactory->createGetValueCall($collectionPlaceholder);

        $assignmentStatement = $valuePlaceholder . ' = ' . $assignmentCall;

        $classDependencies = new ClassDependencyCollection();
        $classDependencies = $classDependencies->merge([
            $collectionCall->getClassDependencies(),
            $assignmentCall->getClassDependencies(),
        ]);

        $variableDependencies = new VariablePlaceholderCollection();
        $variableDependencies = $variableDependencies->merge([
            $collectionCall->getVariableDependencies(),
            $assignmentCall->getVariableDependencies(),
        ]);

        $variableExports = $variableExports->merge([
            $collectionCall->getVariableExports(),
            $assignmentCall->getVariableExports(),
        ]);

        $compilableSource = new CompilableSource(array_merge(
            $collectionCall->getStatements(),
            [
                $assignmentStatement,
            ]
        ));

        $compilableSource = $compilableSource->withClassDependencies($classDependencies);
        $compilableSource = $compilableSource->withVariableDependencies($variableDependencies);
        $compilableSource = $compilableSource->withVariableExports($variableExports);

        return new VariableAssignmentCall($compilableSource, $valuePlaceholder);
    }

    /**
     * @param DomIdentifierInterface $attributeIdentifier
     * @param VariablePlaceholder $valuePlaceholder
     *
     * @return VariableAssignmentCall
     */
    private function createForAttributeValue(
        DomIdentifierInterface $attributeIdentifier,
        VariablePlaceholder $valuePlaceholder
    ): VariableAssignmentCall {
        $assignmentCall = $this->createForAttribute(
            $attributeIdentifier,
            $this->createElementLocatorPlaceholder(),
            $this->createElementPlaceholder(),
            $valuePlaceholder
        );

        $classDependencies = new ClassDependencyCollection();
        $classDependencies = $classDependencies->merge([$assignmentCall->getClassDependencies()]);

        $variableDependencies = new VariablePlaceholderCollection();
        $variableDependencies = $variableDependencies->merge([$assignmentCall->getVariableDependencies()]);

        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->merge([$assignmentCall->getVariableExports()]);
        $variableExports = $variableExports->withAdditionalItems([
            $valuePlaceholder
        ]);

        $compilableSource = new CompilableSource($assignmentCall->getStatements());
        $compilableSource = $compilableSource->withClassDependencies($classDependencies);
        $compilableSource = $compilableSource->withVariableDependencies($variableDependencies);
        $compilableSource = $compilableSource->withVariableExports($variableExports);

        return new VariableAssignmentCall($compilableSource, $valuePlaceholder);
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
        $assignmentCall = $this->createForAttribute(
            $attributeIdentifier,
            $this->createElementLocatorPlaceholder(),
            $this->createElementPlaceholder(),
            $valuePlaceholder
        );

        $existenceAssignmentStatement = sprintf(
            '%s = %s !== null',
            (string) $valuePlaceholder,
            (string) $valuePlaceholder
        );

        $classDependencies = new ClassDependencyCollection();
        $classDependencies = $classDependencies->merge([$assignmentCall->getClassDependencies()]);

        $variableDependencies = new VariablePlaceholderCollection();
        $variableDependencies = $variableDependencies->merge([$assignmentCall->getVariableDependencies()]);

        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->merge([$assignmentCall->getVariableExports()]);
        $variableExports = $variableExports->withAdditionalItems([
            $valuePlaceholder
        ]);

        $compilableSource = new CompilableSource(array_merge(
            $assignmentCall->getStatements(),
            [
                $existenceAssignmentStatement,
            ]
        ));

        $compilableSource = $compilableSource->withClassDependencies($classDependencies);
        $compilableSource = $compilableSource->withVariableDependencies($variableDependencies);
        $compilableSource = $compilableSource->withVariableExports($variableExports);

        return new VariableAssignmentCall($compilableSource, $valuePlaceholder);
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

        $hasAssignmentCall = (new CompilableSource([sprintf(
            '%s = %s',
            (string) $hasVariablePlaceholder,
            (string) $hasCall
        )]))
            ->withClassDependencies($hasCall->getClassDependencies())
            ->withVariableDependencies($hasCall->getVariableDependencies())
            ->withVariableExports($hasCall->getVariableExports());

        $hasVariableAssignmentCall = new VariableAssignmentCall(
            $hasAssignmentCall,
            $hasVariablePlaceholder
        );

        $elementExistsAssertionCall = $this->assertionCallFactory->createValueIsTrueAssertionCall(
            $hasVariableAssignmentCall
        );

        $findStatement = $returnValuePlaceholder . ' = ' . $findCall;

        $classDependencies = new ClassDependencyCollection();
        $classDependencies = $classDependencies->merge([
            $hasCall->getClassDependencies(),
            $findCall->getClassDependencies(),
            $elementLocatorConstructor->getClassDependencies(),
            $elementExistsAssertionCall->getClassDependencies(),
        ]);

        $variableDependencies = new VariablePlaceholderCollection();
        $variableDependencies = $variableDependencies->merge([
            $hasCall->getVariableDependencies(),
            $findCall->getVariableDependencies(),
            $elementLocatorConstructor->getVariableDependencies(),
            $elementExistsAssertionCall->getVariableDependencies(),
        ]);

        $variableExports = $variableExports->merge([
            $hasCall->getVariableExports(),
            $findCall->getVariableExports(),
            $elementLocatorConstructor->getVariableExports(),
            $elementExistsAssertionCall->getVariableExports(),
        ]);

        $compilableSource = new CompilableSource(array_merge(
            [
                $elementLocatorConstructorStatement,
            ],
            $elementExistsAssertionCall->getStatements(),
            [
                $findStatement,
            ]
        ));

        $compilableSource = $compilableSource->withClassDependencies($classDependencies);
        $compilableSource = $compilableSource->withVariableDependencies($variableDependencies);
        $compilableSource = $compilableSource->withVariableExports($variableExports);

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

        $assignmentStatement = sprintf(
            '%s = %s ?? ' . $default,
            $variablePlaceholder,
            $variableAccessLastStatement
        );

        $variableExports = $variableExports->merge([$accessCall->getVariableExports()]);

        $compilableSource = new CompilableSource(array_merge($accessStatements, [
            $assignmentStatement,
        ]));

        $compilableSource = $compilableSource->withClassDependencies($accessCall->getClassDependencies());
        $compilableSource = $compilableSource->withVariableDependencies($accessCall->getVariableDependencies());
        $compilableSource = $compilableSource->withVariableExports($variableExports);

        return new VariableAssignmentCall($compilableSource, $variablePlaceholder);
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

        $assignmentCall = $this->createForScalar($value, $variablePlaceholder);

        $variableExports = $variableExports->merge([$assignmentCall->getVariableExports()]);

        $comparisonStatement = $variablePlaceholder . ' = ' . $variablePlaceholder . ' !== null';

        $statements = array_merge(
            $assignmentCall->getStatements(),
            [
                $comparisonStatement
            ]
        );

        $compilableSource = new CompilableSource($statements);
        $compilableSource = $compilableSource->withClassDependencies($assignmentCall->getClassDependencies());
        $compilableSource = $compilableSource->withVariableDependencies($assignmentCall->getVariableDependencies());
        $compilableSource = $compilableSource->withVariableExports($variableExports);

        return new VariableAssignmentCall($compilableSource, $variablePlaceholder);
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
