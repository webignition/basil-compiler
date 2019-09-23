<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModel\Assertion\AssertableComparisonAssertion;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\Assertion\AssertableExaminedValue;
use webignition\BasilModel\Value\Assertion\AssertableExpectedValue;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\VariableNames;
use webignition\WebDriverElementInspector\Inspector;

trait IsAssertionFunctionalDataProviderTrait
{
    public function isAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'is comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".foo" is "Sibling 2"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'COLLECTION' => '$collection',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$webDriverElementInspector',
                ],
                'additionalPreLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'is comparison, attribute identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".foo".id is "a-sibling"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'ATTRIBUTE' => '$attribute',
                ],
                'additionalPreLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'is comparison, environment examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '$env.TEST1 is "environment value"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
            'is comparison, browser object examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '$browser.size is "1200x1100"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                ],
            ],
            'is comparison, page object examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '$page.title is "A basic page"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                ],
            ],
            'is comparison, element identifier examined value, element identifier expected value' => [
                'fixture' => '/basic.html',
                'assertion' => new AssertableComparisonAssertion(
                    '".foo" is $elements.foo',
                    new AssertableExaminedValue(
                        new DomIdentifierValue(
                            new DomIdentifier(
                                new ElementExpression('.foo', ElementExpressionType::CSS_SELECTOR)
                            )
                        )
                    ),
                    AssertionComparison::IS,
                    new AssertableExpectedValue(
                        new DomIdentifierValue(
                            new DomIdentifier(
                                new ElementExpression('.foo', ElementExpressionType::CSS_SELECTOR)
                            )
                        )
                    )
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'COLLECTION' => '$collection',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$webDriverElementInspector',
                ],
                'additionalPreLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'is comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/basic.html',
                'assertion' => new AssertableComparisonAssertion(
                    '".foo" is $elements.contains_foo.data-foo',
                    new AssertableExaminedValue(
                        new DomIdentifierValue(
                            new DomIdentifier(
                                new ElementExpression('.foo', ElementExpressionType::CSS_SELECTOR)
                            )
                        )
                    ),
                    AssertionComparison::IS,
                    new AssertableExpectedValue(
                        new DomIdentifierValue(
                            (new DomIdentifier(
                                new ElementExpression('.contains-foo', ElementExpressionType::CSS_SELECTOR)
                            ))->withAttributeName('data-foo')
                        )
                    )
                ),
                'variableIdentifiers' => [
                    'ELEMENT' => '$element',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'EXPECTED_VALUE' => '$expectedValue',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$webDriverElementInspector',
                ],
                'additionalPreLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'is comparison, attribute identifier examined value, environment expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".foo".data-environment-value is $env.TEST1'
                ),
                'variableIdentifiers' => [
                    'ENVIRONMENT_VARIABLE_ARRAY' => '$_ENV',
                    'ELEMENT' => '$element',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'EXPECTED_VALUE' => '$expectedValue',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
            ],
            'is comparison, attribute identifier examined value, browser object expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".foo".data-browser-size is $browser.size'
                ),
                'variableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'ELEMENT' => '$element',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'EXPECTED_VALUE' => '$expectedValue',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
            ],
            'is comparison, attribute identifier examined value, page object expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".foo".data-page-title is $page.title'
                ),
                'variableIdentifiers' => [
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'ELEMENT' => '$element',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'EXPECTED_VALUE' => '$expectedValue',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
            ],
        ];
    }
}
