<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\VariableNames;
use webignition\WebDriverElementInspector\Inspector;

trait ExistsAssertionFunctionalDataProviderTrait
{
    public function existsAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'exists comparison, element identifier examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".foo" exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
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
            'exists comparison, attribute identifier examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
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
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '$env.TEST1 exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
            'exists comparison, browser object value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
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
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
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
