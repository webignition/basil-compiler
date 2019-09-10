<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit;

use webignition\BasilModel\Value\BrowserProperty;
use webignition\BasilTranspiler\UnknownObjectPropertyException;

class UnknownObjectPropertyExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testGetValue()
    {
        $value = new BrowserProperty('$browser.foo', 'foo');

        $exception = new UnknownObjectPropertyException($value);

        $this->assertSame($value, $exception->getValue());
    }
}
