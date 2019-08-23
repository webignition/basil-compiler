<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;
use webignition\SymfonyDomCrawlerNavigator\Model\LocatorType;

class ElementLocatorCallFactory
{
    const TEMPLATE = 'new ElementLocator(%s, \'%s\', %s)';
    const DEFAULT_LOCATOR_TYPE = 'LocatorType::CSS_SELECTOR';
    const DEFAULT_SLASH_QUOTE_PLACEHOLDER_VALUE = 'slash-quote-placeholder';

    private $placeholderFactory;

    private $valueTypeToLocatorTypeMap = [
        ValueTypes::XPATH_EXPRESSION => 'LocatorType::XPATH_EXPRESSION',
    ];

    public function __construct(PlaceholderFactory $placeholderFactory)
    {
        $this->placeholderFactory = $placeholderFactory;
    }

    public static function createFactory(): ElementLocatorCallFactory
    {
        return new ElementLocatorCallFactory(
            PlaceholderFactory::createFactory()
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
            $this->escapeLocatorString($identifierValue->getValue()),
            $elementIdentifier->getPosition()
        );

        return new TranspilationResult(
            $content,
            new UseStatementCollection([
                new UseStatement(ElementLocator::class),
                new UseStatement(LocatorType::class),
            ])
        );
    }

    private function escapeLocatorString(string $locatorString): string
    {
        $slashQuotePlaceholder = $this->placeholderFactory->create(
            $locatorString,
            self::DEFAULT_SLASH_QUOTE_PLACEHOLDER_VALUE
        );

        $locatorString = str_replace("\'", $slashQuotePlaceholder, $locatorString);
        $locatorString = str_replace("'", "\'", $locatorString);
        $locatorString = str_replace($slashQuotePlaceholder, "\\\\\'", $locatorString);

        return $locatorString;
    }
}
