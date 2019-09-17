<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Identifier;

use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;

trait AttributeIdentifierDataProviderTrait
{
    public function attributeIdentifierDataProvider(): array
    {
        return [
            'attribute identifier' => [
                'model' => (new DomIdentifier(
                    new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
                ))->withAttributeName('attribute_name'),
            ],
            'invalid attribute identifier: empty attribute name' => [
                'model' => (new DomIdentifier(
                    new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
                ))->withAttributeName(''),
            ],
        ];
    }
}
