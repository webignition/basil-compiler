<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilTranspiler\Action\WaitActionTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Action\UnhandledActionsDataProvider;
use webignition\BasilTranspiler\Tests\DataProvider\Action\WaitActionDataProviderTrait;

class WaitActionTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use WaitActionDataProviderTrait;
    use UnhandledActionsDataProvider;

    /**
     * @var WaitActionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = WaitActionTranspiler::createTranspiler();
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
