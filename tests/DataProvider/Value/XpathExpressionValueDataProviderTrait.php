<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\XpathExpression;

trait XpathExpressionValueDataProviderTrait
{
    public function xpathExpressionValueDataProvider(): array
    {
        return [
            'default xpath expression' => [
                'model' => new XpathExpression('//h1'),
            ],
        ];
    }
}
