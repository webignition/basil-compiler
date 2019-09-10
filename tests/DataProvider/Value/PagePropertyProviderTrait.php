<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\PageProperty;

trait PagePropertyProviderTrait
{
    public function pagePropertyDataProvider(): array
    {
        return [
            'default page property' => [
                'model' => new PageProperty('$page.url', 'url'),
            ],
        ];
    }
}
