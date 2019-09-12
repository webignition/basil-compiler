<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;

trait CssSelectorValueDataProviderTrait
{
    public function cssSelectorValueDataProvider(): array
    {
        return [
            'default css selector' => [
                'model' => new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR),
            ],
        ];
    }
}
