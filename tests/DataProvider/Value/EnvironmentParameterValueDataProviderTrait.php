<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;

trait EnvironmentParameterValueDataProviderTrait
{
    public function environmentParameterValueDataProvider(): array
    {
        return [
            'default page property object' => [
                'model' => new ObjectValue(ObjectValueType::ENVIRONMENT_PARAMETER, '', ''),
            ],
        ];
    }
}
