<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\PageProperty;

trait PageObjectValueDataProviderTrait
{
    public function pageObjectValueDataProvider(): array
    {
        return [
            'default page property object' => [
                'model' => new PageProperty('$page.url', 'url'),
            ],
        ];
    }
}
