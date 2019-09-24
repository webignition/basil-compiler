<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilTranspiler\Action\BrowserOperationActionTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Action\BackActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ClickActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ForwardActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ReloadActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\SubmitActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\UnhandledActionsDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitForActionDataProviderTrait;

class BrowserOperationActionTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use WaitActionDataProviderTrait;
    use WaitForActionDataProviderTrait;
    use UnhandledActionsDataProviderTrait;
    use BackActionDataProviderTrait;
    use ForwardActionDataProviderTrait;
    use ReloadActionDataProviderTrait;
    use ClickActionDataProviderTrait;
    use SubmitActionDataProviderTrait;

    /**
     * @var BrowserOperationActionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = BrowserOperationActionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider backActionDataProvider
     * @dataProvider forwardActionDataProvider
     * @dataProvider reloadActionDataProvider
     */
    public function testHandlesDoesHandle(ActionInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider waitForActionDataProvider
     * @dataProvider waitActionDataProvider
     * @dataProvider clickActionDataProvider
     * @dataProvider submitActionDataProvider
     * @dataProvider unhandledActionsDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->transpiler->handles($model));
    }

    /**
     * @dataProvider transpileNonTranspilableModelDataProvider
     */
    public function testTranspileNonTranspilableModel(object $model, string $expectedExceptionMessage)
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->transpiler->transpile($model);
    }

    public function transpileNonTranspilableModelDataProvider(): array
    {
        return [
            'wrong object type' => [
                'model' => new \stdClass(),
                'expectedExceptionMessage' => 'Non-transpilable model "' . \stdClass::class . '"',
            ],
        ];
    }
}
