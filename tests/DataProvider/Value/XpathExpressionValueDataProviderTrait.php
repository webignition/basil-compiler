<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;

trait XpathExpressionValueDataProviderTrait
{
    public function xpathExpressionValueDataProvider(): array
    {
        return [
            'default xpath expression' => [
                'model' => new ElementExpression('//h1', ElementExpressionType::XPATH_EXPRESSION),
            ],
        ];
    }
}
