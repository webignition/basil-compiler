<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Value;

use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilCompilationSource\CompilationMetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;

use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;

class ValueTranspilerTest extends AbstractTestCase
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
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(
        string $fixture,
        ValueInterface $model,
        CompilationMetadataInterface $expectedCompilationMetadata,
        $expectedExecutedResult,
        array $additionalVariableIdentifiers = []
    ) {
        $compilableSource = $this->transpiler->transpile($model);

        $this->assertEquals($expectedCompilationMetadata, $compilableSource->getCompilationMetadata());

        $executableCall = $this->createExecutableCallWithReturn(
            $compilableSource,
            $fixture,
            array_merge(
                self::VARIABLE_IDENTIFIERS,
                $additionalVariableIdentifiers
            )
        );

        $this->assertEquals($expectedExecutedResult, eval($executableCall));
    }

    public function transpileDataProvider(): array
    {
        return [
            'browser property: size' => [
                'fixture' => '/empty.html',
                'model' => new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.size', 'size'),
                'expectedCompilationMetadata' => (new CompilationMetadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]))->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'WEBDRIVER_DIMENSION',
                    ])),
                'expectedExecutedResult' => '1200x1100',
                'additionalVariableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                ],
            ],
            'page property: title' => [
                'fixture' => '/index.html',
                'model' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.title', 'title'),
                'expectedCompilationMetadata' => (new CompilationMetadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
                'expectedExecutedResult' => 'Test fixture web server default document',
            ],
            'page property: url' => [
                'fixture' => '/index.html',
                'model' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.url', 'url'),
                'expectedCompilationMetadata' => (new CompilationMetadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
                'expectedExecutedResult' => 'http://127.0.0.1:9080/index.html',
            ],
        ];
    }
}
