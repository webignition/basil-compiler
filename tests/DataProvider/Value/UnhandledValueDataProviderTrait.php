<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\AttributeReference;
use webignition\BasilModel\Value\AttributeValue;
use webignition\BasilModel\Value\DataParameter;
use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;
use webignition\BasilModel\Value\ElementReference;
use webignition\BasilModel\Value\PageElementReference;

trait UnhandledValueDataProviderTrait
{
    public function unhandledValueDataProvider(): array
    {
        return [
            'unhandled value: data parameter object' => [
                'model' => new DataParameter('$data.key', 'key'),
            ],
            'unhandled value: element parameter object' => [
                'model' => new ElementReference('', ''),
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
                'model' => new AttributeReference('', ''),
            ],
            'unhandled value: attribute identifier' => [
                'model' => new AttributeValue(
                    new AttributeIdentifier(
                        new ElementIdentifier(
                            new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
                        ),
                        'attribute_name'
                    )
                ),
            ],
        ];
    }
}
