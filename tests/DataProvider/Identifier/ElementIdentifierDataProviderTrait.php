<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Identifier;

use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;

trait ElementIdentifierDataProviderTrait
{
    public function elementIdentifierDataProvider(): array
    {
        return [
            'css selector element identifier' => [
                'model' => TestIdentifierFactory::createElementIdentifier(
                    new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
                ),
            ],
            'xpath expression element identifier' => [
                'model' => TestIdentifierFactory::createElementIdentifier(
                    new ElementExpression('//h1', ElementExpressionType::XPATH_EXPRESSION)
                ),
            ],
        ];
    }
}
