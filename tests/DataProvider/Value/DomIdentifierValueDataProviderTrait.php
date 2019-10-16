<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;

trait DomIdentifierValueDataProviderTrait
{
    public function domIdentifierValueDataProvider(): array
    {
        return [
            'element value' => [
                'model' => new DomIdentifierValue(new DomIdentifier('.selector')),
            ],
            'attribute value' => [
                'model' => new DomIdentifierValue(
                    (new DomIdentifier('.selector'))->withAttributeName('attribute_name')
                ),
            ],
        ];
    }
}
