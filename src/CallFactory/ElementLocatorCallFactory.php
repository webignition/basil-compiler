<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\PlaceholderFactory;
use webignition\BasilTranspiler\SingleQuotedStringEscaper;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;
use webignition\SymfonyDomCrawlerNavigator\Model\LocatorType;

class ElementLocatorCallFactory
{
    const TEMPLATE = 'new ElementLocator(%s)';
    const REQUIRED_ARGUMENTS_TEMPLATE = '%s, \'%s\'';
    const DEFAULT_LOCATOR_TYPE = 'LocatorType::CSS_SELECTOR';

    private $placeholderFactory;
    private $singleQuotedStringEscaper;

    private $valueTypeToLocatorTypeMap = [
        ValueTypes::XPATH_EXPRESSION => 'LocatorType::XPATH_EXPRESSION',
    ];

    public function __construct(
        PlaceholderFactory $placeholderFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
        $this->placeholderFactory = $placeholderFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): ElementLocatorCallFactory
    {
        return new ElementLocatorCallFactory(
            PlaceholderFactory::createFactory(),
            SingleQuotedStringEscaper::create()
        );
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createConstructorCall(ElementIdentifierInterface $elementIdentifier): TranspilationResultInterface
    {
        $identifierValue = $elementIdentifier->getValue();

        if (!$identifierValue instanceof LiteralValueInterface) {
            throw new NonTranspilableModelException($elementIdentifier);
        }

        $arguments = sprintf(
            self::REQUIRED_ARGUMENTS_TEMPLATE,
            $this->valueTypeToLocatorTypeMap[$identifierValue->getType()] ?? self::DEFAULT_LOCATOR_TYPE,
            $this->singleQuotedStringEscaper->escape($identifierValue->getValue())
        );

        $position = $elementIdentifier->getPosition();
        if (null !== $position) {
            $arguments .= ', ' . $position;
        }

        $content = sprintf(self::TEMPLATE, $arguments);

        return new TranspilationResult(
            [
                $content,
            ],
            new UseStatementCollection([
                new UseStatement(ElementLocator::class),
                new UseStatement(LocatorType::class),
            ]),
            new VariablePlaceholderCollection()
        );
    }
}
