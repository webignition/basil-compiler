<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;

trait DomIdentifierValueDataProviderTrait
{
    public function domIdentifierValueDataProvider(): array
    {
        return [
            'default element value' => [
                'model' => new DomIdentifierValue(
                    new DomIdentifier(
                        new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
                    )
                ),
            ],
        ];
    }
}
