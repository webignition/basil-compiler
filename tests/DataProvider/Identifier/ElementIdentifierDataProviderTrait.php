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
                'identifier' => TestIdentifierFactory::createCssElementIdentifier('.selector'),
            ],
            'xpath expression element identifier' => [
                'identifier' => TestIdentifierFactory::createXpathElementIdentifier('//h1'),
            ],
        ];
    }
}
