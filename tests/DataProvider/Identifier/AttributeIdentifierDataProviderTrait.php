<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Identifier;

use webignition\BasilModel\Identifier\DomIdentifier;

trait AttributeIdentifierDataProviderTrait
{
    public function attributeIdentifierDataProvider(): array
    {
        return [
            'attribute identifier' => [
                'model' => (new DomIdentifier('.selector'))->withAttributeName('attribute_name'),
            ],
            'invalid attribute identifier: empty attribute name' => [
                'model' => (new DomIdentifier('.selector'))->withAttributeName(''),
            ],
        ];
    }
}
