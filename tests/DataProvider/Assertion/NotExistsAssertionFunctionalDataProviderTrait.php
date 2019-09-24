<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\VariableNames;

trait NotExistsAssertionFunctionalDataProviderTrait
{
    public function notExistsAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'not-exists comparison, element identifier examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector" not-exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
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
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '$env.INVALID not-exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
        ];
    }
}