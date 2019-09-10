<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Value;

use webignition\BasilModel\Value\ObjectNames;
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
use webignition\BasilTranspiler\UnknownObjectPropertyException;
use webignition\BasilTranspiler\Value\PageObjectValueTranspiler;

class PageObjectValueTranspilerTest extends \PHPUnit\Framework\TestCase
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
     * @var PageObjectValueTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = PageObjectValueTranspiler::createTranspiler();
    }

    /**
     * @dataProvider pageObjectValueDataProvider
     */
    public function testHandlesDoesHandle(ValueInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider browserObjectValueDataProvider
     * @dataProvider elementValueDataProvider
     * @dataProvider environmentParameterValueDataProvider
     * @dataProvider cssSelectorValueDataProvider
     * @dataProvider literalValueDataProvider
     * @dataProvider xpathExpressionValueDataProvider
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

    public function testTranspileThrowsUnknownObjectPropertyException()
    {
        $model = new ObjectValue(
            ValueTypes::PAGE_OBJECT_PROPERTY,
            '$page.foo',
            ObjectNames::PAGE,
            'foo'
        );

        $this->expectException(UnknownObjectPropertyException::class);
        $this->expectExceptionMessage('Unknown object property "foo"');

        $this->transpiler->transpile($model);
    }
}
