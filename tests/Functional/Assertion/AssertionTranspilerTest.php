<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Assertion;

use PHPUnit\Framework\ExpectationFailedException;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\AttributeValue;
use webignition\BasilModel\Value\CssSelector;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\Assertion\AssertionTranspiler;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\Tests\Services\ExecutableCallFactory;
use webignition\BasilTranspiler\VariableNames;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementInspector\Inspector;

class AssertionTranspilerTest extends AbstractTestCase
{
    const DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME = '$domCrawlerNavigator';
    const PHPUNIT_TEST_CASE_VARIABLE_NAME = '$this';

    const VARIABLE_IDENTIFIERS = [
        VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
        VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
    ];

    /**
     * @var AssertionTranspiler
     */
    private $transpiler;

    /**
     * @var ExecutableCallFactory
     */
    private $executableCallFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = AssertionTranspiler::createTranspiler();
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    /**
     * @dataProvider existsComparisonPassingAssertionsDataProvider
     * @dataProvider notExistsComparisonPassingAssertionsDataProvider
     * @dataProvider isComparisonPassingAssertionsDataProvider
     * @dataProvider isNotComparisonPassingAssertionsDataProvider
     * @dataProvider includesComparisonPassingAssertionsDataProvider
     * @dataProvider excludesComparisonPassingAssertionsDataProvider
     * @dataProvider matchesComparisonPassingAssertionsDataProvider
     */
    public function testTranspileForPassingAssertions(
        string $fixture,
        AssertionInterface $assertion,
        array $variableIdentifiers,
        array $additionalSetupLines = [],
        array $additionalUseStatements = []
    ) {
        $transpilationResult = $this->transpiler->transpile($assertion);

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $fixture,
            $additionalSetupLines,
            $additionalUseStatements
        );

        eval($executableCall);
    }

    public function existsComparisonPassingAssertionsDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'exists comparison, element identifier examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo" exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$webDriverElementInspector',
                ],
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'exists comparison, attribute identifier examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '"#a-sibling".class exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
            ],
            'exists comparison, environment examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
            'exists comparison, browser object value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                ],
            ],
            'exists comparison, page object value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                ],
            ],
        ];
    }

    public function notExistsComparisonPassingAssertionsDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'not-exists comparison, element identifier examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" not-exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '"#a-sibling".invalid not-exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
            ],
            'not-exists comparison, environment examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.INVALID not-exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
        ];
    }

    public function isComparisonPassingAssertionsDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'is comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'is comparison, attribute identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'is comparison, environment examined value, scalar expected value' => [
                'fixture' => '/basic.html',
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
                'fixture' => '/basic.html',
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
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'assertion' => new Assertion(
                    '".foo" is $elements.foo',
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.foo')
                        )
                    ),
                    AssertionComparisons::IS,
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.foo')
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'is comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/basic.html',
                'assertion' => new Assertion(
                    '".foo" is $elements.contains_foo.data-foo',
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.foo')
                        )
                    ),
                    AssertionComparisons::IS,
                    new AttributeValue(
                        new AttributeIdentifier(
                            new ElementIdentifier(
                                new CssSelector('.contains-foo')
                            ),
                            'data-foo'
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'is comparison, attribute identifier examined value, environment expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'assertion' => $assertionFactory->createFromAssertionString(
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

    public function isNotComparisonPassingAssertionsDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'is-not comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo" is-not "value"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'COLLECTION' => '$collection',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$webDriverElementInspector',
                ],
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'is-not comparison, attribute identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo".id is-not "value"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'ATTRIBUTE' => '$attribute',
                ],
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'is-not comparison, environment examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 is-not "value"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
            'is-not comparison, browser object examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size is-not "1x1"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                ],
            ],
            'is-not comparison, page object examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title is-not "value"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                ],
            ],
            'is-not comparison, element identifier examined value, element identifier expected value' => [
                'fixture' => '/basic.html',
                'assertion' => new Assertion(
                    '".p-1" is-not $elements.p2',
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.p-1')
                        )
                    ),
                    AssertionComparisons::IS_NOT,
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.p-2')
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'is-not comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/basic.html',
                'assertion' => new Assertion(
                    '".foo" is-not $elements.contains_foo.data-bar',
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.foo')
                        )
                    ),
                    AssertionComparisons::IS_NOT,
                    new AttributeValue(
                        new AttributeIdentifier(
                            new ElementIdentifier(
                                new CssSelector('.contains-foo')
                            ),
                            'data-bar'
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'is-not comparison, attribute identifier examined value, environment expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo".data-environment-value is-not $env.FOO'
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
            'is-not comparison, attribute identifier examined value, browser object expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo".data-foo is-not $browser.size'
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
            'is-not comparison, attribute identifier examined value, page object expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo".data-page-foo is-not $page.title'
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

    public function includesComparisonPassingAssertionsDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'includes comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo" includes "Sibling"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'COLLECTION' => '$collection',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$webDriverElementInspector',
                ],
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'includes comparison, attribute identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo".id includes "sibling"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'ATTRIBUTE' => '$attribute',
                ],
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'includes comparison, environment examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 includes "environment"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
            'includes comparison, browser object examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size includes "200x11"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                ],
            ],
            'includes comparison, page object examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title includes "basic"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                ],
            ],
            'includes comparison, element identifier examined value, element identifier expected value' => [
                'fixture' => '/basic.html',
                'assertion' => new Assertion(
                    '".foo" includes $elements.foo',
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.foo')
                        )
                    ),
                    AssertionComparisons::INCLUDES,
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.foo')
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'includes comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/basic.html',
                'assertion' => new Assertion(
                    '".foo" includes $elements.contains_foo.data-foo',
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.foo')
                        )
                    ),
                    AssertionComparisons::INCLUDES,
                    new AttributeValue(
                        new AttributeIdentifier(
                            new ElementIdentifier(
                                new CssSelector('.contains-foo')
                            ),
                            'data-foo'
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'includes comparison, attribute identifier examined value, environment expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo".data-environment-value includes $env.TEST1'
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
            'includes comparison, attribute identifier examined value, browser object expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo".data-browser-size includes $browser.size'
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
            'includes comparison, attribute identifier examined value, page object expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo".data-page-title includes $page.title'
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

    public function excludesComparisonPassingAssertionsDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'excludes comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'excludes comparison, attribute identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'excludes comparison, environment examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'assertion' => new Assertion(
                    '".p-1" excludes $elements.p2',
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.p-1')
                        )
                    ),
                    AssertionComparisons::EXCLUDES,
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.p-2')
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'excludes comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/basic.html',
                'assertion' => new Assertion(
                    '".foo" excludes $elements.contains_foo.data-bar',
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.foo')
                        )
                    ),
                    AssertionComparisons::INCLUDES,
                    new AttributeValue(
                        new AttributeIdentifier(
                            new ElementIdentifier(
                                new CssSelector('.contains-foo')
                            ),
                            'data-bar'
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'excludes comparison, attribute identifier examined value, environment expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'assertion' => $assertionFactory->createFromAssertionString(
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

    public function matchesComparisonPassingAssertionsDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'matches comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo" matches "/^Sibling [0-9]$/"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'COLLECTION' => '$collection',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$webDriverElementInspector',
                ],
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'matches comparison, attribute identifier examined value, scalar expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo".id matches "/^[a-z]-sib[a-z]{3}g$/"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'ATTRIBUTE' => '$attribute',
                ],
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'matches comparison, environment examined value, scalar expected value' => [
                'fixture' => '/basic.html',
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
                'fixture' => '/basic.html',
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
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title matches "/page$/"'
                ),
                'variableIdentifiers' => [
                    'EXPECTED_VALUE' => '$expectedValue',
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                ],
            ],
            'matches comparison, element identifier examined value, element identifier expected value' => [
                'fixture' => '/basic.html',
                'assertion' => new Assertion(
                    '".p-matches-examined matches $elements.p-matches-expected',
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.p-matches-examined')
                        )
                    ),
                    AssertionComparisons::MATCHES,
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.p-matches-expected')
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'matches comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/basic.html',
                'assertion' => new Assertion(
                    '".foo" matches $elements.matches_foo.data-matches',
                    new ElementValue(
                        new ElementIdentifier(
                            new CssSelector('.foo')
                        )
                    ),
                    AssertionComparisons::MATCHES,
                    new AttributeValue(
                        new AttributeIdentifier(
                            new ElementIdentifier(
                                new CssSelector('.foo')
                            ),
                            'data-matches'
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
                'additionalSetupLines' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new UseStatement(Inspector::class),
                ],
            ],
            'matches comparison, attribute identifier examined value, environment expected value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo".data-environment-value matches $env.MATCHES'
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

    /**
     * @dataProvider transpileForFailingAssertionsDataProvider
     */
    public function testTranspileForFailingAssertions(
        string $fixture,
        AssertionInterface $assertion,
        array $variableIdentifiers,
        string $expectedExpectationFailedExceptionMessage
    ) {
        $transpilationResult = $this->transpiler->transpile($assertion);

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $fixture
        );

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($expectedExpectationFailedExceptionMessage);

        eval($executableCall);
    }

    public function transpileForFailingAssertionsDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'exists comparison, element identifier examined value, element does not exist' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
            'exists comparison, attribute identifier examined value, element does not exist' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
            'exists comparison, attribute identifier examined value, attribute does not exist' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo".attribute_name exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
            'exists comparison, environment examined value, environment variable does not exist' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.FOO exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
        ];
    }

    private function createExecutableCall(
        TranspilationResultInterface $transpilationResult,
        array $variableIdentifiers,
        string $fixture,
        array $additionalSetupLines = [],
        array $additionalUseStatements = []
    ): string {
        return $this->executableCallFactory->create(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            array_merge(
                [
                    '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                    '$domCrawlerNavigator = Navigator::create($crawler); ',
                ],
                $additionalSetupLines
            ),
            new UseStatementCollection(array_merge(
                [
                    new UseStatement(Navigator::class),
                ],
                $additionalUseStatements
            ))
        );
    }
}
