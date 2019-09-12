<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\AssertionExaminedValue;
use webignition\BasilModel\Value\AssertionExpectedValue;
use webignition\BasilModel\Value\AttributeReference;
use webignition\BasilModel\Value\AttributeValue;
use webignition\BasilModel\Value\BrowserProperty;
use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;
use webignition\BasilModel\Value\ElementReference;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\EnvironmentValue;
use webignition\BasilModel\Value\PageProperty;
use webignition\BasilModelFactory\AssertionFactory;

trait IsAssertionDataProviderTrait
{
    public function isAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        $browserProperty = new BrowserProperty('$browser.size', 'size');

        $environmentValue = new EnvironmentValue('$env.KEY', 'KEY');

        $elementValue = new ElementValue(
            new ElementIdentifier(
                new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
            )
        );

        $pageProperty = new PageProperty('$page.url', 'url');

        $attributeValue = new AttributeValue(
            new AttributeIdentifier(
                new ElementIdentifier(
                    new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
                ),
                'attribute_name'
            )
        );

        $elementParameterValue = new ElementReference('', '');
        $attributeParameterValue = new AttributeReference('', '');

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
                'assertion' => new ComparisonAssertion(
                    '',
                    new AssertionExaminedValue($browserProperty),
                    AssertionComparison::IS,
                    new AssertionExpectedValue($elementValue)
                ),
            ],
            'is comparison, browser object examined value, attribute identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '',
                    new AssertionExaminedValue($browserProperty),
                    AssertionComparison::IS,
                    new AssertionExpectedValue($attributeValue)
                ),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'assertion' => new ComparisonAssertion(
                    '',
                    new AssertionExaminedValue($browserProperty),
                    AssertionComparison::IS,
                    new AssertionExpectedValue($environmentValue)
                ),
            ],
            'is comparison, browser object examined value, page object expected value' => [
                'assertion' => new ComparisonAssertion(
                    '',
                    new AssertionExaminedValue($browserProperty),
                    AssertionComparison::IS,
                    new AssertionExpectedValue($pageProperty)
                ),
            ],
            'is comparison, browser object examined value, element parameter expected value' => [
                'assertion' => new ComparisonAssertion(
                    '',
                    new AssertionExaminedValue($browserProperty),
                    AssertionComparison::IS,
                    new AssertionExpectedValue($elementParameterValue)
                ),
            ],
            'is comparison, browser object examined value, attribute parameter expected value' => [
                'assertion' => new ComparisonAssertion(
                    '',
                    new AssertionExaminedValue($browserProperty),
                    AssertionComparison::IS,
                    new AssertionExpectedValue($attributeParameterValue)
                ),
            ],
        ];
    }
}
