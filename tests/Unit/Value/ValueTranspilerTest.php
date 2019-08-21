<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Value;

use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\EnvironmentValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\BrowserObjectValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\ElementValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\EnvironmentParameterValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\LiteralCssSelectorValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\LiteralStringValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\LiteralXpathExpressionValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\PageObjectValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\UnhandledValueDataProviderTrait;
use webignition\BasilTranspiler\Value\ValueTranspiler;

class ValueTranspilerTest extends \PHPUnit\Framework\TestCase
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
     * @var ValueTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = ValueTranspiler::createTranspiler();
    }

    /**
     * @dataProvider browserObjectValueDataProvider
     * @dataProvider elementValueDataProvider
     * @dataProvider environmentParameterValueDataProvider
     * @dataProvider literalCssSelectorValueDataProvider
     * @dataProvider literalStringValueDataProvider
     * @dataProvider literalXpathExpressionValueDataProvider
     * @dataProvider pageObjectValueDataProvider
     */
    public function testHandlesDoesHandle(ValueInterface $value)
    {
        $this->assertTrue($this->transpiler->handles($value));
    }

    /**
     * @dataProvider handlesDoesNotHandleDataProvider
     * @dataProvider unhandledValueDataProvider
     */
    public function testHandlesDoesNotHandle(object $value)
    {
        $this->assertFalse($this->transpiler->handles($value));
    }

    public function handlesDoesNotHandleDataProvider(): array
    {
        return [
            'non-value object' => [
                'value' => new \stdClass(),
            ],
        ];
    }

    /**
     * @dataProvider transpileLiteralStringValueDataProvider
     */
    public function testTranspile(ValueInterface $value, string $expectedString)
    {
        $this->assertSame($expectedString, $this->transpiler->transpile($value));
    }

    public function transpileLiteralStringValueDataProvider(): array
    {
        return [
            'literal string value: string' => [
                'value' => LiteralValue::createStringValue('value'),
                'expectedString' => '"value"',
            ],
            'literal string value: integer' => [
                'value' => LiteralValue::createStringValue('100'),
                'expectedString' => '"100"',
            ],
            'browser object property: size' => [
                'value' => new ObjectValue(
                    ValueTypes::BROWSER_OBJECT_PROPERTY,
                    '$browser.size',
                    ObjectNames::BROWSER,
                    'size'
                ),
                'expectedString' => 'self::$client->getWebDriver()->manage()->window()->getSize()',
            ],
            'page object property: title' => [
                'value' => new ObjectValue(
                    ValueTypes::PAGE_OBJECT_PROPERTY,
                    '$page.title',
                    ObjectNames::PAGE,
                    'title'
                ),
                'expectedString' => 'self::$client->getTitle()',
            ],
            'page object property: url' => [
                'value' => new ObjectValue(
                    ValueTypes::PAGE_OBJECT_PROPERTY,
                    '$page.url',
                    ObjectNames::PAGE,
                    'url'
                ),
                'expectedString' => 'self::$client->getCurrentURL()',
            ],
            'environment parameter value' => [
                'value' => new EnvironmentValue(
                    '$env.KEY',
                    'KEY'
                ),
                'expectedString' => '$_ENV[\'KEY\']',
            ],
            'element identifier, css selector' => [
                'value' => new ElementValue(
                    new ElementIdentifier(
                        LiteralValue::createCssSelectorValue('.selector')
                    )
                ),
                'expectedString' => '".selector"',
            ],
            'element identifier, css selector with non-default position' => [
                'value' => new ElementValue(
                    new ElementIdentifier(
                        LiteralValue::createCssSelectorValue('.selector'),
                        2
                    )
                ),
                'expectedString' => '".selector"',
            ],
            'element identifier, css selector with name' => [
                'value' => new ElementValue(
                    TestIdentifierFactory::createCssElementIdentifier('.selector', null, 'element_name')
                ),
                'expectedString' => '".selector"',
            ],
            'element identifier, xpath expression' => [
                'value' => new ElementValue(
                    new ElementIdentifier(
                        LiteralValue::createXpathExpressionValue('//h1')
                    )
                ),
                'expectedString' => '"//h1"',
            ],
        ];
    }

    public function testTranspileNonTranspilableModel()
    {
        $value = new ObjectValue('foo', '', '', '');

        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "webignition\BasilModel\Value\ObjectValue"');

        $this->transpiler->transpile($value);
    }
}
