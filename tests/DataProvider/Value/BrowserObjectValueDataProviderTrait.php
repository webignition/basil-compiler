<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\BrowserProperty;

trait BrowserObjectValueDataProviderTrait
{
    public function browserObjectValueDataProvider(): array
    {
        return [
            'default browser object property' => [
                'model' => new BrowserProperty('$browser.size', 'size'),
            ],
        ];
    }
}
