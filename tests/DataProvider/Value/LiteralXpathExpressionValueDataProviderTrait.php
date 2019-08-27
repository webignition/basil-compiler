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
                'model' => LiteralValue::createXpathExpressionValue('//h1'),
            ],
        ];
    }
}
