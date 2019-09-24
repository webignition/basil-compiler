<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilTranspiler\Action\WaitForActionTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Action\BackActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ClickActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ForwardActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\ReloadActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\UnhandledActionsDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitActionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitForActionDataProviderTrait;

class WaitForActionTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use WaitActionDataProviderTrait;
    use WaitForActionDataProviderTrait;
    use UnhandledActionsDataProviderTrait;
    use BackActionDataProviderTrait;
    use ForwardActionDataProviderTrait;
    use ReloadActionDataProviderTrait;
    use ClickActionDataProviderTrait;

    /**
     * @var WaitForActionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = WaitForActionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider waitForActionDataProvider
     */
    public function testHandlesDoesHandle(ActionInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider waitActionDataProvider
     * @dataProvider backActionDataProvider
     * @dataProvider forwardActionDataProvider
     * @dataProvider reloadActionDataProvider
     * @dataProvider clickActionDataProvider
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
