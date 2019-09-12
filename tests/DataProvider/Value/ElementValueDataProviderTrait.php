<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;
use webignition\BasilModel\Value\ElementValue;

trait ElementValueDataProviderTrait
{
    public function elementValueDataProvider(): array
    {
        return [
            'default element value' => [
                'model' => new ElementValue(
                    new ElementIdentifier(
                        new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
                    )
                ),
            ],
        ];
    }
}
