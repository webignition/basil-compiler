<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModelFactory\AssertionFactory;

trait ExcludesAssertionDataProviderTrait
{
    public function excludesAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'excludes comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector" excludes "value"'
                ),
            ],
            'excludes comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector".attribute_name excludes "value"'
                ),
            ],
        ];
    }
}
