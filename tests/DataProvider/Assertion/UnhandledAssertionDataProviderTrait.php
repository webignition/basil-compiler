<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Assertion;

use webignition\BasilModelFactory\AssertionFactory;

trait UnhandledAssertionDataProviderTrait
{
    public function unhandledAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'unhandled assertion: non-value object' => [
                'value' => new \stdClass(),
            ],
        ];
    }
}
