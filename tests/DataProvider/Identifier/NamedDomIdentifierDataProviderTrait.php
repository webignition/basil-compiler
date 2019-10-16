<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Identifier;

use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilTranspiler\Model\NamedDomIdentifier;

trait NamedDomIdentifierDataProviderTrait
{
    public function namedDomIdentifierDataProvider(): array
    {
        return [
            'element identifier' => [
                'model' => new NamedDomIdentifier(
                    new DomIdentifier('.selector'),
                    new VariablePlaceholder('ELEMENT_PLACEHOLDER')
                ),
            ],
            'attribute identifier' => [
                'model' => new NamedDomIdentifier(
                    (new DomIdentifier('.selector'))->withAttributeName('attribute_name'),
                    new VariablePlaceholder('ATTRIBUTE_PLACEHOLDER')
                ),
            ],
        ];
    }
}
