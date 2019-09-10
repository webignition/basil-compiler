<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Value;

use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Value\BrowserObjectValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\ElementValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\EnvironmentParameterValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\CssSelectorValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\LiteralValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\XpathExpressionValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\PageObjectValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\UnhandledValueDataProviderTrait;
use webignition\BasilTranspiler\Value\LiteralValueTranspiler;

class LiteralValueTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use BrowserObjectValueDataProviderTrait;
    use ElementValueDataProviderTrait;
    use EnvironmentParameterValueDataProviderTrait;
    use CssSelectorValueDataProviderTrait;
    use LiteralValueDataProviderTrait;
    use XpathExpressionValueDataProviderTrait;
    use PageObjectValueDataProviderTrait;
    use UnhandledValueDataProviderTrait;

    /**
     * @var LiteralValueTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = LiteralValueTranspiler::createTranspiler();
    }

    /**
     * @dataProvider cssSelectorValueDataProvider
     * @dataProvider literalValueDataProvider
     * @dataProvider xpathExpressionValueDataProvider
     */
    public function testHandlesDoesHandle(ValueInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider browserObjectValueDataProvider
     * @dataProvider elementValueDataProvider
     * @dataProvider environmentParameterValueDataProvider
     * @dataProvider pageObjectValueDataProvider
     * @dataProvider unhandledValueDataProvider
     */
    public function testHandlesDoesNotHandle(ValueInterface $model)
    {
        $this->assertFalse($this->transpiler->handles($model));
    }

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "webignition\BasilModel\Value\ObjectValue"');

        $model = new ObjectValue(ValueTypes::DATA_PARAMETER, '', '', '');

        $this->transpiler->transpile($model);
    }
}
