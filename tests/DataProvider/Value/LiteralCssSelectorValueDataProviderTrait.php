<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\LiteralValue;

trait LiteralCssSelectorValueDataProviderTrait
{
    public function literalCssSelectorValueDataProvider(): array
    {
        return [
            'default literal string' => [
                'model' => LiteralValue::createCssSelectorValue('.selector'),
            ],
        ];
    }
}
