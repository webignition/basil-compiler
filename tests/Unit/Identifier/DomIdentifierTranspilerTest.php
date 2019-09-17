<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Identifier;

use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\Identifier\DomIdentifierTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Identifier\AttributeIdentifierDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Identifier\ElementIdentifierDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Identifier\UnhandledIdentifierDataProviderTrait;

class DomIdentifierTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use AttributeIdentifierDataProviderTrait;
    use ElementIdentifierDataProviderTrait;
    use UnhandledIdentifierDataProviderTrait;

    /**
     * @var DomIdentifierTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = DomIdentifierTranspiler::createTranspiler();
    }

    /**
     * @dataProvider attributeIdentifierDataProvider
     * @dataProvider elementIdentifierDataProvider
     */
    public function testHandlesDoesHandle(DomIdentifierInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    /**
     * @dataProvider unhandledIdentifierDataProvider
     */
    public function testHandlesDoesNotHandle(object $model)
    {
        $this->assertFalse($this->transpiler->handles($model));
    }

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "stdClass"');

        $model = new \stdClass();

        $this->transpiler->transpile($model);
    }
}
