<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit;

use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilTranspiler\UnknownValueTypeException;

class UnknownValueTypeExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testGetValue()
    {
        $value = new ObjectValue('foo', '', '', '');

        $exception = new UnknownValueTypeException($value);

        $this->assertSame($value, $exception->getValue());
    }
}
