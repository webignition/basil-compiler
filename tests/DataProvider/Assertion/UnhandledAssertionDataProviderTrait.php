<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\AttributeValue;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\EnvironmentValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilModelFactory\AssertionFactory;

trait UnhandledAssertionDataProviderTrait
{
    public function unhandledAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        $browserObjectValue = new ObjectValue(
            ValueTypes::BROWSER_OBJECT_PROPERTY,
            '$browser.size',
            ObjectNames::BROWSER,
            'size'
        );

        $environmentValue = new EnvironmentValue('$env.KEY', 'KEY');

        $elementValue = new ElementValue(
            new ElementIdentifier(
                LiteralValue::createCssSelectorValue('.selector')
            )
        );

        $pageObjectValue = new ObjectValue(
            ValueTypes::PAGE_OBJECT_PROPERTY,
            '$page.url',
            ObjectNames::PAGE,
            'url'
        );

        $attributeValue = new AttributeValue(
            new AttributeIdentifier(
                new ElementIdentifier(
                    LiteralValue::createCssSelectorValue('.selector')
                ),
                'attribute_name'
            )
        );

        $elementParameterValue = new ObjectValue(ValueTypes::ELEMENT_PARAMETER, '', '', '');
        $attributeParameterValue = new ObjectValue(ValueTypes::ATTRIBUTE_PARAMETER, '', '', '');

        return [
            'includes comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" includes "value"'
                ),
            ],
            'includes comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name includes "value"'
                ),
            ],
            'excludes comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" excludes "value"'
                ),
            ],
            'excludes comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name excludes "value"'
                ),
            ],
            'matches comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" matches "/^value/"'
                ),
            ],
            'matches comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name excludes "/^value/"'
                ),
            ],
        ];
    }
}
