<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Identifier;

use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Identifier\ReferenceIdentifier;
use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;
use webignition\BasilModel\Value\ElementReference;
use webignition\BasilModel\Value\PageElementReference;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;

trait UnhandledIdentifierDataProviderTrait
{
    public function unhandledIdentifierDataProvider(): array
    {
        return [
            'invalid attribute identifier: empty attribute name' => [
                'model' => new AttributeIdentifier(
                    new ElementIdentifier(
                        new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
                    ),
                    ''
                ),
            ],
            'page element reference' => [
                'model' => TestIdentifierFactory::createPageElementReferenceIdentifier(
                    new PageElementReference(
                        'page_import_name.elements.element_name',
                        'page_import_name',
                        'element_name'
                    )
                ),
            ],
            'element parameter' => [
                'model' => ReferenceIdentifier::createElementReferenceIdentifier(
                    new ElementReference('$elements.element_name', 'element_name')
                ),
            ],
        ];
    }
}
