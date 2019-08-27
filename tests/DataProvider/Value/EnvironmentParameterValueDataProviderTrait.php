<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\EnvironmentValue;

trait EnvironmentParameterValueDataProviderTrait
{
    public function environmentParameterValueDataProvider(): array
    {
        return [
            'default page property object' => [
                'value' => new EnvironmentValue('', ''),
            ],
        ];
    }
}
