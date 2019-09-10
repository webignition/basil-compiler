<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\LiteralValue;

trait LiteralValueDataProviderTrait
{
    public function literalValueDataProvider(): array
    {
        return [
            'default literal string' => [
                'model' => new LiteralValue('model'),
            ],
        ];
    }
}
