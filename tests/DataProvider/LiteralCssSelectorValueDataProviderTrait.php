<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider;

use webignition\BasilModel\Value\LiteralValue;

trait LiteralCssSelectorValueDataProviderTrait
{
    public function literalCssSelectorValueDataProvider(): array
    {
        return [
            'default literal string' => [
                'value' => LiteralValue::createCssSelectorValue('.selector'),
            ],
        ];
    }
}
