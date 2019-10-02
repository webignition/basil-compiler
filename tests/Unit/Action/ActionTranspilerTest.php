<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilTranspiler\Action\ActionTranspiler;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Action\BackActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ClickActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ForwardActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ReloadActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\SetActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\SubmitActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\UnhandledActionsDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitForActionDataProviderTrait;

class ActionTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use WaitActionDataProviderTrait;
    use WaitForActionDataProviderTrait;
    use UnhandledActionsDataProviderTrait;
    use BackActionDataProviderTrait;
    use ForwardActionDataProviderTrait;
    use ReloadActionDataProviderTrait;
    use ClickActionDataProviderTrait;
    use SubmitActionDataProviderTrait;
    use SetActionDataProviderTrait;

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
     * @dataProvider waitForActionDataProvider
     * @dataProvider backActionDataProvider
     * @dataProvider forwardActionDataProvider
     * @dataProvider reloadActionDataProvider
     * @dataProvider clickActionDataProvider
     * @dataProvider submitActionDataProvider
     * @dataProvider setActionDataProvider
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
     * @dataProvider waitForActionDataProvider
     * @dataProvider backActionDataProvider
     * @dataProvider forwardActionDataProvider
     * @dataProvider reloadActionDataProvider
     * @dataProvider clickActionDataProvider
     * @dataProvider setActionDataProvider
     */
    public function testTranspileDoesNotFail(ActionInterface $model)
    {
        $compilableSource = $this->transpiler->transpile($model);

        $this->assertInstanceOf(CompilableSourceInterface::class, $compilableSource);
    }

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "stdClass"');

        $model = new \stdClass();

        $this->transpiler->transpile($model);
    }
}
