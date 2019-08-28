<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;
use webignition\SymfonyDomCrawlerNavigator\Model\LocatorType;

class ElementLocatorCallFactory
{
    const TEMPLATE = 'new ElementLocator(%s, \'%s\', %s)';
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
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    public function createConstructorCall(ElementIdentifierInterface $elementIdentifier): TranspilationResult
    {
        $identifierValue = $elementIdentifier->getValue();

        if (!$identifierValue instanceof LiteralValueInterface) {
            throw new NonTranspilableModelException($elementIdentifier);
        }

        $content = sprintf(
            self::TEMPLATE,
            $this->valueTypeToLocatorTypeMap[$identifierValue->getType()] ?? self::DEFAULT_LOCATOR_TYPE,
            $this->singleQuotedStringEscaper->escape($identifierValue->getValue()),
            $elementIdentifier->getPosition()
        );

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
