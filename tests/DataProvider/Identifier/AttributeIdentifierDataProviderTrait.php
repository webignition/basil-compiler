<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Identifier;

use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;

trait AttributeIdentifierDataProviderTrait
{
    public function attributeIdentifierDataProvider(): array
    {
        return [
            'attribute identifier' => [
                'model' => new AttributeIdentifier(
                    new ElementIdentifier(
                        new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
                    ),
                    'attribute_name'
                ),
            ],
        ];
    }
}
