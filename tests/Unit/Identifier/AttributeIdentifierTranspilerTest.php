<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Identifier;

use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\AttributeIdentifierInterface;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\CssSelector;
use webignition\BasilTranspiler\Identifier\AttributeIdentifierTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Identifier\AttributeIdentifierDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Identifier\ElementIdentifierDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Identifier\UnhandledIdentifierDataProviderTrait;

class AttributeIdentifierTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use AttributeIdentifierDataProviderTrait;
    use ElementIdentifierDataProviderTrait;
    use UnhandledIdentifierDataProviderTrait;

    /**
     * @var AttributeIdentifierTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = AttributeIdentifierTranspiler::createTranspiler();
    }

    /**
     * @dataProvider attributeIdentifierDataProvider
     */
    public function testHandlesDoesHandle(AttributeIdentifierInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider elementIdentifierDataProvider
     * @dataProvider unhandledIdentifierDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->transpiler->handles($model));
    }

    /**
     * @dataProvider transpileNonTranspilableModelDataProvider
     */
    public function testTranspileNonTranspilableModel(object $model, string $expectedExceptionMessage)
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->transpiler->transpile($model);
    }

    public function transpileNonTranspilableModelDataProvider(): array
    {
        return [
            'wrong object type' => [
                'model' => new \stdClass(),
                'expectedExceptionMessage' => 'Non-transpilable model "' . \stdClass::class . '"',
            ],
            'missing attribute name' => [
                'model' => new AttributeIdentifier(
                    new ElementIdentifier(
                        new CssSelector('.selector')
                    ),
                    ''
                ),
                'expectedExceptionMessage' => 'Non-transpilable model "' . AttributeIdentifier::class . '"',
            ],
        ];
    }
}
