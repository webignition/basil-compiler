<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Assertion;

use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Assertion\IsAssertion;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\Assertion\ExistsComparisonTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\ExcludesAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\ExistsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IncludesAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IsNotAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\MatchesAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\NotExistsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\UnhandledAssertionDataProviderTrait;

class ExistsComparisonTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use ExcludesAssertionDataProviderTrait;
    use ExistsAssertionDataProviderTrait;
    use IncludesAssertionDataProviderTrait;
    use IsAssertionDataProviderTrait;
    use IsNotAssertionDataProviderTrait;
    use MatchesAssertionDataProviderTrait;
    use NotExistsAssertionDataProviderTrait;
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
     * @dataProvider notExistsAssertionDataProvider
     */
    public function testHandlesDoesHandle(AssertionInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider excludesAssertionDataProvider
     * @dataProvider includesAssertionDataProvider
     * @dataProvider isAssertionDataProvider
     * @dataProvider isNotAssertionDataProvider
     * @dataProvider matchesAssertionDataProvider
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
                'expectedExceptionMessage' => 'Non-transpilable model "' . IsAssertion::class . '"',
            ],
        ];
    }
}
