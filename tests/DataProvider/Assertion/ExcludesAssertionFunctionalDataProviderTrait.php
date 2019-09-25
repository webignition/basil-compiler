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
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\VariableNames;
use webignition\WebDriverElementInspector\Inspector;

trait ExcludesAssertionFunctionalDataProviderTrait
{
    public function excludesAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'excludes comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".foo" excludes "value"'
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
            'excludes comparison, attribute identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".foo".id excludes "value"'
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
            'excludes comparison, environment examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '$env.TEST1 excludes "foo"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
            'excludes comparison, browser object examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '$browser.size excludes "1x2"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                ],
            ],
            'excludes comparison, page object examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '$page.title excludes "value"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                ],
            ],
            'excludes comparison, element identifier examined value, element identifier expected value' => [
                'fixture' => '/basic.html',
                'assertion' => new AssertableComparisonAssertion(
                    '".p-1" excludes $elements.p2',
                    new AssertableExaminedValue(
                        new DomIdentifierValue(
                            new DomIdentifier('.p-1')
                        )
                    ),
                    AssertionComparison::EXCLUDES,
                    new AssertableExpectedValue(
                        new DomIdentifierValue(
                            new DomIdentifier('.p-2')
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
            'excludes comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/basic.html',
                'assertion' => new AssertableComparisonAssertion(
                    '".foo" excludes $elements.contains_foo.data-bar',
                    new AssertableExaminedValue(
                        new DomIdentifierValue(
                            new DomIdentifier('.foo')
                        )
                    ),
                    AssertionComparison::EXCLUDES,
                    new AssertableExpectedValue(
                        new DomIdentifierValue(
                            (new DomIdentifier('.contains-foo'))->withAttributeName('data-bar')
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
            'excludes comparison, attribute identifier examined value, environment expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".foo".data-environment-value-excludes excludes $env.TEST1'
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
            'excludes comparison, attribute identifier examined value, browser object expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".foo".data-browser-size-excludes excludes $browser.size'
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
            'excludes comparison, attribute identifier examined value, page object expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".foo".data-page-title-excludes excludes $page.title'
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
