<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Identifier;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\Identifier\IdentifierTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\ElementIdentifierDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\UnhandledIdentifierDataProviderTrait;
use webignition\BasilTranspiler\VariableNames;

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

    /**
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(IdentifierInterface $identifier, string $expectedString)
    {
        $variableIdentifiers = [
            VariableNames::DOM_CRAWLER_NAVIGATOR => '$navigator',
        ];

        $this->assertSame($expectedString, $this->transpiler->transpile($identifier, $variableIdentifiers));
    }

    public function transpileDataProvider(): array
    {
        return [
            'css selector, selector only' => [
                'identifier' => TestIdentifierFactory::createCssElementIdentifier('.selector'),
                'expectedString' =>
                    '$navigator->findElement(new ElementLocator(LocatorType::CSS_SELECTOR, \'.selector\', 1))',
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
