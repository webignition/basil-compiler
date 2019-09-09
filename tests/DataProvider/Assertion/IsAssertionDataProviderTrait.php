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

trait IsAssertionDataProviderTrait
{
    public function isAssertionDataProvider(): array
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
            'is comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" is "value"'
                ),
            ],
            'is comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name is "value"'
                ),
            ],
            'is comparison, browser object examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size is "value"'
                ),
            ],
            'is comparison, environment examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.KEY is "value"'
                ),
            ],
            'is comparison, page object examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title is "value"'
                ),
            ],
            'is comparison, element parameter examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$elements.element_name is "value"'
                ),
            ],
            'is comparison, attribute parameter examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$elements.element_name.attribute_name is "value"'
                ),
            ],
            'is comparison, page element reference examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    'page_import_name.elements.element_name is "value"'
                ),
            ],
            'is comparison, browser object examined value, element identifier expected value' => [
                'assertion' => new Assertion(
                    '',
                    $browserObjectValue,
                    AssertionComparisons::IS,
                    $elementValue
                ),
            ],
            'is comparison, browser object examined value, attribute identifier expected value' => [
                'assertion' => new Assertion(
                    '',
                    $browserObjectValue,
                    AssertionComparisons::IS,
                    $attributeValue
                ),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'assertion' => new Assertion(
                    '',
                    $browserObjectValue,
                    AssertionComparisons::IS,
                    $environmentValue
                ),
            ],
            'is comparison, browser object examined value, page object expected value' => [
                'assertion' => new Assertion(
                    '',
                    $browserObjectValue,
                    AssertionComparisons::IS,
                    $pageObjectValue
                ),
            ],
            'is comparison, browser object examined value, element parameter expected value' => [
                'assertion' => new Assertion(
                    '',
                    $browserObjectValue,
                    AssertionComparisons::IS,
                    $elementParameterValue
                ),
            ],
            'is comparison, browser object examined value, attribute parameter expected value' => [
                'assertion' => new Assertion(
                    '',
                    $browserObjectValue,
                    AssertionComparisons::IS,
                    $attributeParameterValue
                ),
            ],
        ];
    }
}
