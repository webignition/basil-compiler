<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit;

use webignition\BasilTranspiler\Model\ClassDependency;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\UseStatementTranspiler;

class UseStatementTranspilerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UseStatementTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = UseStatementTranspiler::createTranspiler();
    }

    public function testHandlesDoesHandle()
    {
        $model = new ClassDependency(ClassDependency::class);

        $this->assertTrue($this->transpiler->handles($model));
    }

    public function testHandlesDoesNotHandle()
    {
        $model = new \stdClass();

        $this->assertFalse($this->transpiler->handles($model));
    }

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "stdClass"');

        $model = new \stdClass();

        $this->transpiler->transpile($model);
    }
}
