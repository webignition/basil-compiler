<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider;

use webignition\BasilModel\Value\LiteralValue;

trait LiteralStringValueDataProviderTrait
{
    public function literalStringValueDataProvider(): array
    {
        return [
            'default literal string' => [
                'value' => LiteralValue::createStringValue('value'),
            ],
        ];
    }
}
