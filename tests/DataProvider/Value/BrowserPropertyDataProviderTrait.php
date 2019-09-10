<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\BrowserProperty;

trait BrowserPropertyDataProviderTrait
{
    public function browserPropertyDataProvider(): array
    {
        return [
            'default browser property' => [
                'model' => new BrowserProperty('$browser.size', 'size'),
            ],
        ];
    }
}
