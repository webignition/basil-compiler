<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Assertion;

use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Assertion\IncludesAssertion;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\Assertion\MatchesComparisonTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\ExcludesAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\ExistsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IncludesAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IsNotAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\MatchesAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\NotExistsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\UnhandledAssertionDataProviderTrait;

class MatchesComparisonTranspilerTest extends \PHPUnit\Framework\TestCase
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
     * @var MatchesComparisonTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = MatchesComparisonTranspiler::createTranspiler();
    }

    /**
     * @dataProvider matchesAssertionDataProvider
     */
    public function testHandlesDoesHandle(AssertionInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider excludesAssertionDataProvider
     * @dataProvider existsAssertionDataProvider
     * @dataProvider includesAssertionDataProvider
     * @dataProvider isAssertionDataProvider
     * @dataProvider isNotAssertionDataProvider
     * @dataProvider notExistsAssertionDataProvider
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
                'model' => $assertionFactory->createFromAssertionString('".selector" includes "value"'),
                'expectedExceptionMessage' => 'Non-transpilable model "' . IncludesAssertion::class . '"',
            ],
        ];
    }
}
