<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit;

use webignition\BasilTranspiler\NonTranspilableModelException;

class NonTranspilableModelExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testGetValue()
    {
        $model = new \stdClass();

        $exception = new NonTranspilableModelException($model);

        $this->assertSame($model, $exception->getModel());
    }
}
