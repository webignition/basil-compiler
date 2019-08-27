<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\LiteralValue;

trait LiteralXpathExpressionValueDataProviderTrait
{
    public function literalXpathExpressionValueDataProvider(): array
    {
        return [
            'default literal string' => [
                'value' => LiteralValue::createXpathExpressionValue('//h1'),
            ],
        ];
    }
}
