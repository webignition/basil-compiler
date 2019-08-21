<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Identifier;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilTranspiler\Identifier\IdentifierTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\ElementIdentifierDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\UnhandledIdentifierDataProviderTrait;

class IdentifierTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use ElementIdentifierDataProviderTrait;
    use UnhandledIdentifierDataProviderTrait;

    /**
     * @var IdentifierTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = IdentifierTranspiler::createTranspiler();
    }

    /**
     * @dataProvider elementIdentifierDataProvider
     */
    public function testHandlesDoesHandle(ElementIdentifierInterface $value)
    {
        $this->assertTrue($this->transpiler->handles($value));
    }

    /**
     * @dataProvider handlesDoesNotHandleDataProvider
     * @dataProvider unhandledIdentifierDataProvider
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

//    /**
//     * @dataProvider transpileLiteralStringValueDataProvider
//     */
//    public function testTranspile(ValueInterface $value, string $expectedString)
//    {
//        $this->assertSame($expectedString, $this->transpiler->transpile($value));
//    }
//
//    public function transpileLiteralStringValueDataProvider(): array
//    {
//        return [
//            'literal string value: string' => [
//                'value' => LiteralValue::createStringValue('value'),
//                'expectedString' => '"value"',
//            ],
//            'literal string value: integer' => [
//                'value' => LiteralValue::createStringValue('100'),
//                'expectedString' => '"100"',
//            ],
//            'browser object property: size' => [
//                'value' => new ObjectValue(
//                    ValueTypes::BROWSER_OBJECT_PROPERTY,
//                    '$browser.size',
//                    ObjectNames::BROWSER,
//                    'size'
//                ),
//                'expectedString' => 'self::$client->getWebDriver()->manage()->window()->getSize()',
//            ],
//            'page object property: title' => [
//                'value' => new ObjectValue(
//                    ValueTypes::PAGE_OBJECT_PROPERTY,
//                    '$page.title',
//                    ObjectNames::PAGE,
//                    'title'
//                ),
//                'expectedString' => 'self::$client->getTitle()',
//            ],
//            'page object property: url' => [
//                'value' => new ObjectValue(
//                    ValueTypes::PAGE_OBJECT_PROPERTY,
//                    '$page.url',
//                    ObjectNames::PAGE,
//                    'url'
//                ),
//                'expectedString' => 'self::$client->getCurrentURL()',
//            ],
//            'environment parameter value' => [
//                'value' => new EnvironmentValue(
//                    '$env.KEY',
//                    'KEY'
//                ),
//                'expectedString' => '$_ENV[\'KEY\']',
//            ],
//            'element identifier, css selector' => [
//                'value' => new ElementValue(
//                    new ElementIdentifier(
//                        LiteralValue::createCssSelectorValue('.selector')
//                    )
//                ),
//                'expectedString' => '".selector"',
//            ],
//            'element identifier, css selector with non-default position' => [
//                'value' => new ElementValue(
//                    new ElementIdentifier(
//                        LiteralValue::createCssSelectorValue('.selector'),
//                        2
//                    )
//                ),
//                'expectedString' => '".selector"',
//            ],
//            'element identifier, css selector with name' => [
//                'value' => new ElementValue(
//                    TestIdentifierFactory::createCssElementIdentifier('.selector', null, 'element_name')
//                ),
//                'expectedString' => '".selector"',
//            ],
//            'element identifier, xpath expression' => [
//                'value' => new ElementValue(
//                    new ElementIdentifier(
//                        LiteralValue::createXpathExpressionValue('//h1')
//                    )
//                ),
//                'expectedString' => '"//h1"',
//            ],
//        ];
//    }

    public function testTranspileNonTranspilableModel()
    {
        $value = new ObjectValue('foo', '', '', '');

        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "webignition\BasilModel\Value\ObjectValue"');

        $this->transpiler->transpile($value);
    }
}
