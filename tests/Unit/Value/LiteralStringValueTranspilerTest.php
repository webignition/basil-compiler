<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Value;

use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\AttributeValue;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\EnvironmentValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\Value\LiteralStringValueTranspiler;
use webignition\BasilTranspiler\Value\ValueTypeTranspilerInterface;

class LiteralStringValueTranspilerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LiteralStringValueTranspiler|ValueTypeTranspilerInterface
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = LiteralStringValueTranspiler::createTranspiler();
    }

    /**
     * @dataProvider handlesDoesHandleDataProvider
     * @dataProvider handlesDoesNotHandleDataProvider
     */
    public function testHandles(ValueInterface $value, bool $expectedHandles)
    {
        $this->assertSame($expectedHandles, $this->transpiler->handles($value));
    }

    public function handlesDoesHandleDataProvider(): array
    {
        $expectedHandles = true;

        return [
            'literal string' => [
                'value' => LiteralValue::createStringValue('value'),
                'expectedHandles' => $expectedHandles,
            ],
        ];
    }

    public function handlesDoesNotHandleDataProvider(): array
    {
        $expectedHandles = false;

        return [
            'literal css selector' => [
                'value' => LiteralValue::createCssSelectorValue('.selector'),
                'expectedHandles' => $expectedHandles,
            ],
            'literal xpath expression' => [
                'value' => LiteralValue::createCssSelectorValue('//h1'),
                'expectedHandles' => $expectedHandles,
            ],
            'browser object property' => [
                'value' => new ObjectValue(ValueTypes::BROWSER_OBJECT_PROPERTY, '', '', ''),
                'expectedHandles' => $expectedHandles,
            ],
            'data parameter' => [
                'value' => new ObjectValue(ValueTypes::DATA_PARAMETER, '', '', ''),
                'expectedHandles' => $expectedHandles,
            ],
            'element parameter' => [
                'value' => new ObjectValue(ValueTypes::ELEMENT_PARAMETER, '', '', ''),
                'expectedHandles' => $expectedHandles,
            ],
            'page element reference' => [
                'value' => new ObjectValue(ValueTypes::PAGE_ELEMENT_REFERENCE, '', '', ''),
                'expectedHandles' => $expectedHandles,
            ],
            'page object property' => [
                'value' => new ObjectValue(ValueTypes::PAGE_OBJECT_PROPERTY, '', '', ''),
                'expectedHandles' => $expectedHandles,
            ],
            'attribute parameter' => [
                'value' => new ObjectValue(ValueTypes::ATTRIBUTE_PARAMETER, '', '', ''),
                'expectedHandles' => $expectedHandles,
            ],
            'environment parameter' => [
                'value' => new EnvironmentValue('', ''),
                'expectedHandles' => $expectedHandles,
            ],
            'element identifier' => [
                'value' => new ElementValue(
                    new ElementIdentifier(
                        LiteralValue::createCssSelectorValue('.selector')
                    )
                ),
                'expectedHandles' => $expectedHandles,
            ],
            'attribute identifier' => [
                'value' => new AttributeValue(
                    new AttributeIdentifier(
                        new ElementIdentifier(
                            LiteralValue::createCssSelectorValue('.selector')
                        ),
                        'attribute_name'
                    )
                ),
                'expectedHandles' => $expectedHandles,
            ],
        ];
    }

    public function testTranspileDoesNotHandle()
    {
        $this->assertNull($this->transpiler->transpile(LiteralValue::createCssSelectorValue('.selector')));
    }
}
