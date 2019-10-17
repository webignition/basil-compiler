<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\VariableNames;
use webignition\WebDriverElementInspector\Inspector;

trait MatchesAssertionFunctionalDataProviderTrait
{
    public function matchesAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        $additionalCompilationMetadata = (new Metadata())->withClassDependencies(
            new ClassDependencyCollection([
                new ClassDependency(Inspector::class),
            ])
        );

        return [
            'matches comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" matches "/^\.selector [a-z]+$/"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'COLLECTION' => '$collection',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$webDriverElementInspector',
                ],
                'additionalSetupStatements' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalCompilationMetadata' => $additionalCompilationMetadata,
            ],
            'matches comparison, attribute identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute matches "/^[a-z]+ content$/"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'ATTRIBUTE' => '$attribute',
                ],
                'additionalSetupStatements' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalCompilationMetadata' => $additionalCompilationMetadata,
            ],
            'matches comparison, environment examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 matches "/^environment/"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
            'matches comparison, browser object examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size matches "/[0-9]+x[0-9]+/"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                ],
            ],
            'matches comparison, page object examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title matches "/fixture$/"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                ],
            ],
            'matches comparison, element identifier examined value, element identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => new ComparisonAssertion(
                    '".matches-examined matches $elements.matches-expected',
                    DomIdentifierValue::create('.matches-examined'),
                    AssertionComparison::MATCHES,
                    DomIdentifierValue::create('.matches-expected')
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'COLLECTION' => '$collection',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$webDriverElementInspector',
                ],
                'additionalSetupStatements' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalCompilationMetadata' => $additionalCompilationMetadata,
            ],
            'matches comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => new ComparisonAssertion(
                    '".selector" matches $elements.element_name.data-matches-content',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::MATCHES,
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withAttributeName('data-matches-content')
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
                'additionalSetupStatements' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalCompilationMetadata' => $additionalCompilationMetadata,
            ],
            'matches comparison, attribute identifier examined value, environment expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-environment-value matches $env.MATCHES'
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
        ];
    }
}
