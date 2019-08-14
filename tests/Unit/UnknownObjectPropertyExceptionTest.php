<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit;

use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\UnknownObjectPropertyException;

class UnknownObjectPropertyExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testGetValue()
    {
        $value = new ObjectValue(
            ValueTypes::BROWSER_OBJECT_PROPERTY,
            '$browser.foo',
            ObjectNames::BROWSER,
            'foo'
        );

        $exception = new UnknownObjectPropertyException($value);

        $this->assertSame($value, $exception->getValue());
    }
}
