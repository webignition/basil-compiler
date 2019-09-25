<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Identifier;

use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;

trait ElementIdentifierDataProviderTrait
{
    public function elementIdentifierDataProvider(): array
    {
        return [
            'element identifier' => [
                'model' => TestIdentifierFactory::createElementIdentifier('.selector'),
            ],
        ];
    }
}
