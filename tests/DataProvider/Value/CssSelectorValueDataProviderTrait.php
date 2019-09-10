<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Value;

use webignition\BasilModel\Value\CssSelector;

trait CssSelectorValueDataProviderTrait
{
    public function cssSelectorValueDataProvider(): array
    {
        return [
            'default css selector' => [
                'model' => new CssSelector('.selector'),
            ],
        ];
    }
}
