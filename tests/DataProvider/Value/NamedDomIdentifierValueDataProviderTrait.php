<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilTranspiler\Model\NamedDomIdentifierValue;

trait NamedDomIdentifierValueDataProviderTrait
{
    public function namedDomIdentifierValueDataProvider(): array
    {
        return [
            'element value' => [
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(new DomIdentifier('.selector')),
                    new VariablePlaceholder('ELEMENT')
                ),
            ],
            'attribute value' => [
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withAttributeName('attribute_name')
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
            ],
        ];
    }
}
