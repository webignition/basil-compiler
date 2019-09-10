<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Identifier;

use webignition\BasilModel\Value\CssSelector;
use webignition\BasilModel\Value\XpathExpression;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;

trait ElementIdentifierDataProviderTrait
{
    public function elementIdentifierDataProvider(): array
    {
        return [
            'css selector element identifier' => [
                'model' => TestIdentifierFactory::createElementIdentifier(new CssSelector('.selector')),
            ],
            'xpath expression element identifier' => [
                'model' => TestIdentifierFactory::createElementIdentifier(new XpathExpression('//h1')),
            ],
        ];
    }
}
