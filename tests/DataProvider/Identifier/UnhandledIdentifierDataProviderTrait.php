<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Identifier;

use webignition\BasilModel\Identifier\ReferenceIdentifier;
use webignition\BasilModel\Value\DomIdentifierReference;
use webignition\BasilModel\Value\DomIdentifierReferenceType;
use webignition\BasilModel\Value\PageElementReference;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;

trait UnhandledIdentifierDataProviderTrait
{
    public function unhandledIdentifierDataProvider(): array
    {
        return [
            'page element reference' => [
                'model' => TestIdentifierFactory::createPageElementReferenceIdentifier(
                    new PageElementReference(
                        'page_import_name.elements.element_name',
                        'page_import_name',
                        'element_name'
                    )
                ),
            ],
            'attribute reference' => [
                'model' => ReferenceIdentifier::createElementReferenceIdentifier(
                    new DomIdentifierReference(
                        DomIdentifierReferenceType::ATTRIBUTE,
                        '$elements.element_name.attribute_name',
                        'element_name.attribute_name'
                    )
                ),
            ],
            'element reference' => [
                'model' => ReferenceIdentifier::createElementReferenceIdentifier(
                    new DomIdentifierReference(
                        DomIdentifierReferenceType::ELEMENT,
                        '$elements.element_name',
                        'element_name'
                    )
                ),
            ],
        ];
    }
}
