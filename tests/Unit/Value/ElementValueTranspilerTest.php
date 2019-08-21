<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Value;

use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\BrowserObjectValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\ElementValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\EnvironmentParameterValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\LiteralCssSelectorValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\LiteralStringValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\LiteralXpathExpressionValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\PageObjectValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\UnhandledValueDataProviderTrait;
use webignition\BasilTranspiler\Value\ElementValueTranspiler;

class ElementValueTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use BrowserObjectValueDataProviderTrait;
    use ElementValueDataProviderTrait;
    use EnvironmentParameterValueDataProviderTrait;
    use LiteralCssSelectorValueDataProviderTrait;
    use LiteralStringValueDataProviderTrait;
    use LiteralXpathExpressionValueDataProviderTrait;
    use PageObjectValueDataProviderTrait;
    use UnhandledValueDataProviderTrait;

    /**
     * @var ElementValueTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = ElementValueTranspiler::createTranspiler();
    }

    /**
     * @dataProvider elementValueDataProvider
     */
    public function testHandlesDoesHandle(ValueInterface $value)
    {
        $this->assertTrue($this->transpiler->handles($value));
    }

    /**
     * @dataProvider browserObjectValueDataProvider
     * @dataProvider environmentParameterValueDataProvider
     * @dataProvider literalCssSelectorValueDataProvider
     * @dataProvider literalStringValueDataProvider
     * @dataProvider literalXpathExpressionValueDataProvider
     * @dataProvider pageObjectValueDataProvider
     * @dataProvider unhandledValueDataProvider
     */
    public function testHandlesDoesNotHandle(ValueInterface $value)
    {
        $this->assertFalse($this->transpiler->handles($value));
    }

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "webignition\BasilModel\Value\ObjectValue"');

        $value = new ObjectValue(ValueTypes::DATA_PARAMETER, '', '', '');

        $this->transpiler->transpile($value);
    }
}
