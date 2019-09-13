<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModelFactory\AssertionFactory;

trait MatchesAssertionDataProviderTrait
{
    public function matchesAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'matches comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector" matches "/^value/"'
                ),
            ],
            'matches comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector".attribute_name matches "/^value/"'
                ),
            ],
        ];
    }
}
