<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilModel\Value\ValueTypes;

class ElementLocatorFactory
{
    const TEMPLATE = 'new ElementLocator(%s, \'%s\', %s)';
    const DEFAULT_LOCATOR_TYPE = 'LocatorType::CSS_SELECTOR';
    const DEFAULT_SLASH_QUOTE_PLACEHOLDER_VALUE = 'slash-quote-placeholder';
    const SLASH_QUOTE_PLACEHOLDER = '{{ %s }}';

    private $valueTypeToLocatorTypeMap = [
        ValueTypes::XPATH_EXPRESSION => 'LocatorType::XPATH_EXPRESSION',
    ];

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     *
     * @return string
     *
     * @throws NonTranspilableModelException
     */
    public function createElementLocatorConstructorCall(ElementIdentifierInterface $elementIdentifier): string
    {
        $identifierValue = $elementIdentifier->getValue();

        if (!$identifierValue instanceof LiteralValueInterface) {
            throw new NonTranspilableModelException($elementIdentifier);
        }

        return sprintf(
            self::TEMPLATE,
            $this->valueTypeToLocatorTypeMap[$identifierValue->getType()] ?? self::DEFAULT_LOCATOR_TYPE,
            $this->escapeLocatorString($identifierValue->getValue()),
            $elementIdentifier->getPosition()
        );
    }

    private function escapeLocatorString(string $locatorString): string
    {
        $slashQuotePlaceholder = $this->createSlashQuotePlaceholder($locatorString);

        $locatorString = str_replace("\'", $slashQuotePlaceholder, $locatorString);
        $locatorString = str_replace("'", "\'", $locatorString);
        $locatorString = str_replace($slashQuotePlaceholder, "\\\\\'", $locatorString);

        return $locatorString;
    }

    private function createSlashQuotePlaceholder(string $locatorString)
    {
        $slashQuotePlaceholder = sprintf(self::SLASH_QUOTE_PLACEHOLDER, self::DEFAULT_SLASH_QUOTE_PLACEHOLDER_VALUE);
        $slashQuotePlaceholderMutationCount = 0;

        while (substr_count($locatorString, $slashQuotePlaceholder) > 0) {
            $slashQuotePlaceholderMutationCount++;
            $slashQuotePlaceholder = sprintf(
                self::SLASH_QUOTE_PLACEHOLDER,
                self::DEFAULT_SLASH_QUOTE_PLACEHOLDER_VALUE . $slashQuotePlaceholderMutationCount
            );
        }

        return $slashQuotePlaceholder;
    }
}
