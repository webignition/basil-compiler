<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\Model\ClassDependency;
use webignition\BasilTranspiler\VariableNames;
use webignition\WebDriverElementInspector\Inspector;

trait ExistsAssertionFunctionalDataProviderTrait
{
    public function existsAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'exists comparison, element identifier examined value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$webDriverElementInspector',
                ],
                'additionalSetupStatements' => [
                    '$webDriverElementInspector = Inspector::create();',
                ],
                'additionalUseStatements' => [
                    new ClassDependency(Inspector::class),
                ],
            ],
            'exists comparison, attribute identifier examined value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
            ],
            'exists comparison, environment examined value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
            'exists comparison, browser object value' => [
                'fixture' => '/empty.html',
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
                'fixture' => '/empty.html',
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
}
