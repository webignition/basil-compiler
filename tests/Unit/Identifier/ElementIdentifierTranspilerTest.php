<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Identifier;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\Identifier\ElementIdentifierTranspiler;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\AttributeIdentifierDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\ElementIdentifierDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\UnhandledIdentifierDataProviderTrait;

class ElementIdentifierTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use AttributeIdentifierDataProviderTrait;
    use ElementIdentifierDataProviderTrait;
    use UnhandledIdentifierDataProviderTrait;

    /**
     * @var ElementIdentifierTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = ElementIdentifierTranspiler::createTranspiler();
    }

    /**
     * @dataProvider elementIdentifierDataProvider
     */
    public function testHandlesDoesHandle(ElementIdentifierInterface $value)
    {
        $this->assertTrue($this->transpiler->handles($value));
    }

    /**
     * @dataProvider attributeIdentifierDataProvider
     * @dataProvider unhandledIdentifierDataProvider
     */
    public function testHandlesDoesNotHandle(object $value)
    {
        $this->assertFalse($this->transpiler->handles($value));
    }

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "webignition\BasilModel\Value\ObjectValue"');

        $value = new ObjectValue(ValueTypes::DATA_PARAMETER, '', '', '');

        $this->transpiler->transpile($value);
    }
}
