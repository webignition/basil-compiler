<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Assertion;

use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\Assertion\AssertionTranspiler;
use webignition\BasilTranspiler\Assertion\ExistsComparisonTranspiler;
use webignition\BasilTranspiler\Identifier\AttributeIdentifierTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\ExistsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\UnhandledAssertionDataProviderTrait;

class ExistsComparisonTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use ExistsAssertionDataProviderTrait;
    use UnhandledAssertionDataProviderTrait;

    /**
     * @var ExistsComparisonTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = ExistsComparisonTranspiler::createTranspiler();
    }

    /**
     * @dataProvider existsAssertionDataProvider
     */
    public function testHandlesDoesHandle(AssertionInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider unhandledAssertionDataProvider
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
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'wrong object type' => [
                'model' => new \stdClass(),
                'expectedExceptionMessage' => 'Non-transpilable model "' . \stdClass::class . '"',
            ],
            'wrong comparison type' => [
                'model' => $assertionFactory->createFromAssertionString('".selector" is "value"'),
                'expectedExceptionMessage' => 'Non-transpilable model "' . Assertion::class . '"',
            ],
        ];
    }
}
