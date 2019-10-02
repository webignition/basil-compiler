<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Value;

use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Value\BrowserPropertyDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\DomIdentifierValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\EnvironmentParameterValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\LiteralValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\PagePropertyProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\UnhandledValueDataProviderTrait;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;
use webignition\BasilTranspiler\Model\VariablePlaceholder;

class ValueTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use BrowserPropertyDataProviderTrait;
    use DomIdentifierValueDataProviderTrait;
    use EnvironmentParameterValueDataProviderTrait;
    use LiteralValueDataProviderTrait;
    use PagePropertyProviderTrait;
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
     * @dataProvider browserPropertyDataProvider
     * @dataProvider environmentParameterValueDataProvider
     * @dataProvider literalValueDataProvider
     * @dataProvider pagePropertyDataProvider
     */
    public function testHandlesDoesHandle(ValueInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider domIdentifierValueDataProvider
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
    public function testTranspile(ValueInterface $model, CompilableSourceInterface $expectedTranspilableSource)
    {
        $this->assertEquals($expectedTranspilableSource, $this->transpiler->transpile($model));
    }

    public function transpileDataProvider(): array
    {
        return [
            'literal string value: string' => [
                'value' => new LiteralValue('value'),
                'expectedTranspilableSource' => new CompilableSource(
                    ['"value"'],
                    new ClassDependencyCollection(),
                    new VariablePlaceholderCollection(),
                    new VariablePlaceholderCollection()
                ),
            ],
            'literal string value: integer' => [
                'value' => new LiteralValue('100'),
                'expectedTranspilableSource' => new CompilableSource(
                    ['"100"'],
                    new ClassDependencyCollection(),
                    new VariablePlaceholderCollection(),
                    new VariablePlaceholderCollection()
                ),
            ],
            'environment parameter value' => [
                'value' => new ObjectValue(
                    ObjectValueType::ENVIRONMENT_PARAMETER,
                    '$env.KEY',
                    'KEY'
                ),
                'expectedTranspilableSource' => new CompilableSource(
                    [(string) new VariablePlaceholder(VariableNames::ENVIRONMENT_VARIABLE_ARRAY) . '[\'KEY\']'],
                    new ClassDependencyCollection(),
                    VariablePlaceholderCollection::createCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    new VariablePlaceholderCollection()
                ),
            ],
            'browser object value, size' => [
                'value' => new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.size', 'size'),
                'expectedTranspilableSource' => new VariableAssignmentCall(
                    new CompilableSource(
                        [
                        '{{ WEBDRIVER_DIMENSION }} = '
                        . '{{ PANTHER_CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                        '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight()',
                        ],
                        new ClassDependencyCollection(),
                        new VariablePlaceholderCollection([
                            new VariablePlaceholder('WEBDRIVER_DIMENSION'),
                            new VariablePlaceholder('BROWSER_SIZE'),
                            new VariablePlaceholder(VariableNames::PANTHER_CLIENT),
                        ]),
                        new VariablePlaceholderCollection()
                    ),
                    new VariablePlaceholder('BROWSER_SIZE')
                ),
            ],
        ];
    }

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "stdClass"');

        $model = new \stdClass();

        $this->transpiler->transpile($model);
    }
}
