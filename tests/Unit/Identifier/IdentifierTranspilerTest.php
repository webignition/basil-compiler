<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Identifier;

use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilTranspiler\Identifier\IdentifierTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Identifier\AttributeIdentifierDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Identifier\ElementIdentifierDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Identifier\UnhandledIdentifierDataProviderTrait;

class IdentifierTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use AttributeIdentifierDataProviderTrait;
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
     * @dataProvider attributeIdentifierDataProvider
     * @dataProvider elementIdentifierDataProvider
     */
    public function testHandlesDoesHandle(IdentifierInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider handlesDoesNotHandleDataProvider
     * @dataProvider unhandledIdentifierDataProvider
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

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "stdClass"');

        $model = new \stdClass();

        $this->transpiler->transpile($model);
    }
}
