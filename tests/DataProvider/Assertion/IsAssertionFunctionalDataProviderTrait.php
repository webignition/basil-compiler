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

trait IsAssertionFunctionalDataProviderTrait
{
    public function isAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        $additionalMetadata = (new Metadata())->withClassDependencies(
            new ClassDependencyCollection([
                new ClassDependency(Inspector::class),
            ])
        );

        return [
            'is comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" is ".selector content"'
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
                'additionalMetadata' => $additionalMetadata,
            ],
            'is comparison, attribute identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute is "attribute content"'
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
                'additionalMetadata' => $additionalMetadata,
            ],
            'is comparison, environment examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 is "environment value"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
            'is comparison, browser object examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title is "Test fixture web server default document"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                ],
            ],
            'is comparison, element identifier examined value, element identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => new ComparisonAssertion(
                    '".selector" is $elements.element_name',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::IS,
                    DomIdentifierValue::create('.selector')
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
                'additionalMetadata' => $additionalMetadata,
            ],
            'is comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => new ComparisonAssertion(
                    '".selector" is $elements.element_name.data-is-selector-content',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::IS,
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withAttributeName('data-is-selector-content')
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
                'additionalMetadata' => $additionalMetadata,
            ],
            'is comparison, attribute identifier examined value, environment expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-environment-value is $env.TEST1'
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
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-browser-size is $browser.size'
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
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-page-title is $page.title'
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
            'is comparison, select element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".select-none-selected" is "none-selected-1"'
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
                'additionalMetadata' => $additionalMetadata,
            ],
            'is comparison, select element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".select-has-selected" is "has-selected-2"'
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
                'additionalMetadata' => $additionalMetadata,
            ],
            'is comparison, option collection element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".select-none-selected option" is "none-selected-1"'
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
                'additionalMetadata' => $additionalMetadata,
            ],
            'is comparison, option collection element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".select-has-selected option" is "has-selected-2"'
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
                'additionalMetadata' => $additionalMetadata,
            ],
            'is comparison, radio group element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '"input[name=radio-not-checked]" is ""'
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
                'additionalMetadata' => $additionalMetadata,
            ],
            'is comparison, radio group element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '"input[name=radio-checked]" is "checked-2"'
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
                'additionalMetadata' => $additionalMetadata,
            ],
        ];
    }
}
