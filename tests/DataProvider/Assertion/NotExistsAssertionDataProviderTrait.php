<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModelFactory\AssertionFactory;

trait NotExistsAssertionDataProviderTrait
{
    public function notExistsAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'not-exists comparison, element identifier examined value' => [
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector" not-exists'
                ),
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector".attribute_name not-exists'
                ),
            ],
        ];
    }
}
