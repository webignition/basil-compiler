<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModelFactory\AssertionFactory;

trait IncludesAssertionDataProviderTrait
{
    public function includesAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'includes comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector" includes "value"'
                ),
            ],
            'includes comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector".attribute_name includes "value"'
                ),
            ],
        ];
    }
}
