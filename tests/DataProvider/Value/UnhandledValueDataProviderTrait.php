<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\DomIdentifierReference;
use webignition\BasilModel\Value\DomIdentifierReferenceType;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\PageElementReference;

trait UnhandledValueDataProviderTrait
{
    public function unhandledValueDataProvider(): array
    {
        return [
            'unhandled value: data parameter object' => [
                'model' => new ObjectValue(ObjectValueType::DATA_PARAMETER, '$data.key', 'key'),
            ],
            'unhandled value: element parameter object' => [
                'model' => new DomIdentifierReference(DomIdentifierReferenceType::ELEMENT, '', ''),
            ],
            'unhandled value: page element reference' => [
                'model' => new PageElementReference('', '', ''),
            ],
            'unhandled value: malformed page property object' => [
                'model' => new PageElementReference(
                    '',
                    '',
                    ''
                ),
            ],
            'unhandled value: attribute parameter' => [
                'model' => new DomIdentifierReference(DomIdentifierReferenceType::ATTRIBUTE, '', ''),
            ],
        ];
    }
}
