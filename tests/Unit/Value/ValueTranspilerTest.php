<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Value;

use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\EnvironmentValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Value\BrowserObjectValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\ElementValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\EnvironmentParameterValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\LiteralCssSelectorValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\LiteralStringValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\LiteralXpathExpressionValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\PageObjectValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\UnhandledValueDataProviderTrait;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;
use webignition\BasilTranspiler\VariablePlaceholder;

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
    public function testHandlesDoesHandle(ValueInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider handlesDoesNotHandleDataProvider
     * @dataProvider unhandledValueDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->transpiler->handles($model));
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
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(ValueInterface $model, TranspilationResult $expectedTranspilationResult)
    {
        $variableIdentifiers = [
            VariableNames::PANTHER_CLIENT => 'self::$client',
            VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
        ];

        $this->assertEquals($expectedTranspilationResult, $this->transpiler->transpile($model, $variableIdentifiers));
    }

    public function transpileDataProvider(): array
    {
        return [
            'literal string value: string' => [
                'value' => LiteralValue::createStringValue('value'),
                'expectedTranspilationResult' => new TranspilationResult('"value"'),
            ],
            'literal string value: integer' => [
                'value' => LiteralValue::createStringValue('100'),
                'expectedTranspilationResult' => new TranspilationResult('"100"'),
            ],
            'environment parameter value' => [
                'value' => new EnvironmentValue(
                    '$env.KEY',
                    'KEY'
                ),
                'expectedTranspilationResult' => new TranspilationResult(
                    (string) new VariablePlaceholder(VariableNames::ENVIRONMENT_VARIABLE_ARRAY) . '[\'KEY\']'
                ),
            ],
            'element identifier, css selector' => [
                'value' => new ElementValue(
                    new ElementIdentifier(
                        LiteralValue::createCssSelectorValue('.selector')
                    )
                ),
                'expectedTranspilationResult' => new TranspilationResult('".selector"'),
            ],
            'element identifier, css selector with non-default position' => [
                'value' => new ElementValue(
                    new ElementIdentifier(
                        LiteralValue::createCssSelectorValue('.selector'),
                        2
                    )
                ),
                'expectedTranspilationResult' => new TranspilationResult('".selector"'),
            ],
            'element identifier, css selector with name' => [
                'value' => new ElementValue(
                    TestIdentifierFactory::createCssElementIdentifier('.selector', null, 'element_name')
                ),
                'expectedTranspilationResult' => new TranspilationResult('".selector"'),
            ],
            'element identifier, xpath expression' => [
                'value' => new ElementValue(
                    new ElementIdentifier(
                        LiteralValue::createXpathExpressionValue('//h1')
                    )
                ),
                'expectedTranspilationResult' => new TranspilationResult('"//h1"'),
            ],
        ];
    }

    public function testTranspileNonTranspilableModel()
    {
        $model = new ObjectValue('foo', '', '', '');

        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "webignition\BasilModel\Value\ObjectValue"');

        $this->transpiler->transpile($model);
    }
}
