<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Value;

use webignition\BasilModel\Value\EnvironmentValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\UnknownValueTypeException;
use webignition\BasilTranspiler\Value\ValueTranspiler;

class ValueTranspilerTest extends \PHPUnit\Framework\TestCase
{
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
        ];
    }

    public function testTranspileUnknownValueType()
    {
        $value = new ObjectValue('foo', '', '', '');

        $this->expectException(UnknownValueTypeException::class);
        $this->expectExceptionMessage('Unknown value type "foo"');

        $this->transpiler->transpile($value);
    }
}
