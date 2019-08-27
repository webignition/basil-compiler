<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Identifier;

use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;

trait ElementIdentifierDataProviderTrait
{
    public function elementIdentifierDataProvider(): array
    {
        return [
            'css selector element identifier' => [
                'model' => TestIdentifierFactory::createCssElementIdentifier('.selector'),
            ],
            'xpath expression element identifier' => [
                'model' => TestIdentifierFactory::createXpathElementIdentifier('//h1'),
            ],
        ];
    }
}
