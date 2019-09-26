<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModelFactory\AssertionFactory;

trait IsAssertionDataProviderTrait
{
    public function isAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        $browserProperty = new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.size', 'size');
        $environmentValue = new ObjectValue(ObjectValueType::ENVIRONMENT_PARAMETER, '$env.KEY', 'KEY');
        $elementValue = new DomIdentifierValue(new DomIdentifier('.selector'));
        $pageProperty = new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.url', 'url');

        $attributeValue = new DomIdentifierValue(
            (new DomIdentifier('.selector'))->withAttributeName('attribute_name')
        );

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
            'is comparison, browser object examined value, element identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '',
                    $browserProperty,
                    AssertionComparison::IS,
                    $elementValue
                ),
            ],
            'is comparison, browser object examined value, attribute identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '',
                    $browserProperty,
                    AssertionComparison::IS,
                    $attributeValue
                ),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'assertion' => new ComparisonAssertion(
                    '',
                    $browserProperty,
                    AssertionComparison::IS,
                    $environmentValue
                ),
            ],
            'is comparison, browser object examined value, page object expected value' => [
                'assertion' => new ComparisonAssertion(
                    '',
                    $browserProperty,
                    AssertionComparison::IS,
                    $pageProperty
                ),
            ],
        ];
    }
}
