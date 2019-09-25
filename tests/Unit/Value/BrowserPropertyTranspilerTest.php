<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Value;

use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Value\BrowserPropertyDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\DomIdentifierValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\EnvironmentParameterValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\LiteralValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\PagePropertyProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\UnhandledValueDataProviderTrait;
use webignition\BasilTranspiler\UnknownObjectPropertyException;
use webignition\BasilTranspiler\Value\BrowserPropertyTranspiler;

class BrowserPropertyTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use BrowserPropertyDataProviderTrait;
    use DomIdentifierValueDataProviderTrait;
    use EnvironmentParameterValueDataProviderTrait;
    use LiteralValueDataProviderTrait;
    use PagePropertyProviderTrait;
    use UnhandledValueDataProviderTrait;

    /**
     * @var BrowserPropertyTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = BrowserPropertyTranspiler::createTranspiler();
    }

    /**
     * @dataProvider browserPropertyDataProvider
     */
    public function testHandlesDoesHandle(ValueInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider domIdentifierValueDataProvider
     * @dataProvider environmentParameterValueDataProvider
     * @dataProvider literalValueDataProvider
     * @dataProvider pagePropertyDataProvider
     * @dataProvider unhandledValueDataProvider
     */
    public function testHandlesDoesNotHandle(ValueInterface $model)
    {
        $this->assertFalse($this->transpiler->handles($model));
    }

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "stdClass"');

        $model = new \stdClass();

        $this->transpiler->transpile($model);
    }

    public function testTranspileThrowsUnknownObjectPropertyException()
    {
        $model = new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.foo', 'foo');

        $this->expectException(UnknownObjectPropertyException::class);
        $this->expectExceptionMessage('Unknown object property "foo"');

        $this->transpiler->transpile($model);
    }
}
