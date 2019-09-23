<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilTranspiler\Action\ActionTranspiler;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Action\UnhandledActionsDataProvider;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitActionDataProviderTrait;

class ActionTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use WaitActionDataProviderTrait;
    use UnhandledActionsDataProvider;

    /**
     * @var ActionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = ActionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider waitActionDataProvider
     */
    public function testHandlesDoesHandle(ActionInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider unhandledActionsDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->transpiler->handles($model));
    }

    /**
     * @dataProvider waitActionDataProvider
     */
    public function testTranspileDoesNotFail(ActionInterface $model)
    {
        $transpilationResult = $this->transpiler->transpile($model);

        $this->assertInstanceOf(TranspilationResultInterface::class, $transpilationResult);
    }

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "stdClass"');

        $model = new \stdClass();

        $this->transpiler->transpile($model);
    }
}
