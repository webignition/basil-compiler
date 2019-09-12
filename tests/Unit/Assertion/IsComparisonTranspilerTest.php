<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Assertion;

use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Assertion\MatchesAssertion;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\Assertion\IsComparisonTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\ExcludesAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\ExistsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IncludesAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IsNotAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\MatchesAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\NotExistsAssertionDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\UnhandledAssertionDataProviderTrait;

class IsComparisonTranspilerTest extends \PHPUnit\Framework\TestCase
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
     * @var IsComparisonTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = IsComparisonTranspiler::createTranspiler();
    }

    /**
     * @dataProvider isAssertionDataProvider
     * @dataProvider isNotAssertionDataProvider
     */
    public function testHandlesDoesHandle(AssertionInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider excludesAssertionDataProvider
     * @dataProvider existsAssertionDataProvider
     * @dataProvider includesAssertionDataProvider
     * @dataProvider matchesAssertionDataProvider
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
                'model' => $assertionFactory->createFromAssertionString('".selector" matches "value"'),
                'expectedExceptionMessage' => 'Non-transpilable model "' . MatchesAssertion::class . '"',
            ],
        ];
    }
}
