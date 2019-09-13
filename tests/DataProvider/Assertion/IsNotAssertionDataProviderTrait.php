<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModelFactory\AssertionFactory;

trait IsNotAssertionDataProviderTrait
{
    public function isNotAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'is-not comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector" is-not "value"'
                ),
            ],
            'is-not comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector".attribute_name is-not "value"'
                ),
            ],
        ];
    }
}
