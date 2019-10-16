<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit;

use webignition\BasilTranspiler\Model\NamedDomIdentifierInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Identifier\NamedDomIdentifierDataProviderTrait;
use webignition\BasilTranspiler\NamedDomIdentifierTranspiler;

class NamedDomIdentifierTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use NamedDomIdentifierDataProviderTrait;

    /**
     * @var NamedDomIdentifierTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = NamedDomIdentifierTranspiler::createTranspiler();
    }

    /**
     * @dataProvider namedDomIdentifierDataProvider
     */
    public function testHandlesDoesHandle(NamedDomIdentifierInterface $model)
    {
        $this->assertTrue($this->transpiler->handles($model));
    }

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "stdClass"');

        $model = new \stdClass();

        $this->transpiler->transpile($model);
    }
}
