<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\AttributeValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;

trait UnhandledValueDataProviderTrait
{
    public function unhandledValueDataProvider(): array
    {
        return [
            'unhandled value: data parameter object' => [
                'model' => new ObjectValue(
                    ValueTypes::DATA_PARAMETER,
                    '$data.key',
                    ObjectNames::DATA,
                    'key'
                ),
            ],
            'unhandled value: element parameter object' => [
                'model' => new ObjectValue(ValueTypes::ELEMENT_PARAMETER, '', '', ''),
            ],
            'unhandled value: page element reference' => [
                'model' => new ObjectValue(ValueTypes::PAGE_ELEMENT_REFERENCE, '', '', ''),
            ],
            'unhandled value: malformed page property object' => [
                'model' => new ObjectValue(
                    ValueTypes::PAGE_OBJECT_PROPERTY,
                    '',
                    '',
                    ''
                ),
            ],
            'unhandled value: attribute parameter' => [
                'model' => new ObjectValue(ValueTypes::ATTRIBUTE_PARAMETER, '', '', ''),
            ],
            'unhandled value: attribute identifier' => [
                'model' => new AttributeValue(
                    new AttributeIdentifier(
                        new ElementIdentifier(
                            LiteralValue::createCssSelectorValue('.selector')
                        ),
                        'attribute_name'
                    )
                ),
            ],
        ];
    }
}
