<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider;

use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\LiteralValue;

trait AttributeIdentifierDataProviderTrait
{
    public function attributeIdentifierDataProvider(): array
    {
        return [
            'attribute identifier' => [
                'identifier' => new AttributeIdentifier(
                    new ElementIdentifier(
                        LiteralValue::createCssSelectorValue('.selector')
                    ),
                    'attribute_name'
                ),
            ],
        ];
    }
}
