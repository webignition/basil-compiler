<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Value;

use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\Model\NamedDomIdentifierValue;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\Tests\DataProvider\Identifier\NamedDomIdentifierDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\BrowserPropertyDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\NamedDomIdentifierValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\EnvironmentParameterValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\LiteralValueDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\PagePropertyProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Value\UnhandledValueDataProviderTrait;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;
use webignition\DomElementLocator\ElementLocator;

class ValueTranspilerTest extends \PHPUnit\Framework\TestCase
{
    use BrowserPropertyDataProviderTrait;
    use NamedDomIdentifierValueDataProviderTrait;
    use NamedDomIdentifierDataProviderTrait;
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
     * @dataProvider namedDomIdentifierValueDataProvider
     * @dataProvider environmentParameterValueDataProvider
     * @dataProvider literalValueDataProvider
     * @dataProvider pagePropertyDataProvider
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
    public function testTranspile(
        ValueInterface $model,
        array $expectedStatements,
        MetadataInterface $expectedCompilationMetadata
    ) {
        $compilableSource = $this->transpiler->transpile($model);

        $this->assertEquals($expectedStatements, $compilableSource->getStatements());
        $this->assertEquals($expectedCompilationMetadata, $compilableSource->getMetadata());
    }

    public function transpileDataProvider(): array
    {
        return [
            'literal string value: string' => [
                'value' => new LiteralValue('value'),
                'expectedStatements' => [
                    '"value"',
                ],
                'expectedCompilationMetadata' => new Metadata(),
            ],
            'literal string value: integer' => [
                'value' => new LiteralValue('100'),
                'expectedStatements' => [
                    '"100"',
                ],
                'expectedCompilationMetadata' => new Metadata(),
            ],
            'environment parameter value' => [
                'value' => new ObjectValue(
                    ObjectValueType::ENVIRONMENT_PARAMETER,
                    '$env.KEY',
                    'KEY'
                ),
                'expectedStatements' => [
                    (string) new VariablePlaceholder(VariableNames::ENVIRONMENT_VARIABLE_ARRAY) . '[\'KEY\']',
                ],
                'expectedCompilationMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ])),
            ],
            'browser property, size' => [
                'value' => new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.size', 'size'),
                'expectedStatements' => [
                    '{{ WEBDRIVER_DIMENSION }} = {{ PANTHER_CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                    . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight()',
                ],
                'expectedCompilationMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]))->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'WEBDRIVER_DIMENSION',
                    ])),
            ],
            'page property, url' => [
                'value' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.url', 'url'),
                'expectedStatements' => [
                    '{{ PANTHER_CLIENT }}->getCurrentURL()',
                ],
                'expectedCompilationMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
            ],
            'page property, title' => [
                'value' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.title', 'title'),
                'expectedStatements' => [
                    '{{ PANTHER_CLIENT }}->getTitle()',
                ],
                'expectedCompilationMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
            ],
            'element value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(new DomIdentifier('.selector')),
                    new VariablePlaceholder('ELEMENT_NO_PARENT')
                ),
                'expectedStatements' => [
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ ELEMENT_NO_PARENT }} = {{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ ELEMENT_NO_PARENT }} = {{ WEBDRIVER_ELEMENT_INSPECTOR }}->getValue({{ ELEMENT_NO_PARENT }})'
                ],
                'expectedCompilationMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT_NO_PARENT',
                    ])),
            ],
            'element value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withParentIdentifier(new DomIdentifier('.parent'))
                    ),
                    new VariablePlaceholder('ELEMENT_HAS_PARENT')
                ),
                'expectedStatements' => [
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}'
                    . '->has(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ ELEMENT_HAS_PARENT }} = {{ DOM_CRAWLER_NAVIGATOR }}'
                    . '->find(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ ELEMENT_HAS_PARENT }} = {{ WEBDRIVER_ELEMENT_INSPECTOR }}->getValue({{ ELEMENT_HAS_PARENT }})'
                ],
                'expectedCompilationMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT_HAS_PARENT',
                    ])),
            ],
            'attribute value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withAttributeName('attribute_name')
                    ),
                    new VariablePlaceholder('ELEMENT_NO_PARENT')
                ),
                'expectedStatements' => [
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ ELEMENT_NO_PARENT }} = {{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ ELEMENT_NO_PARENT }} = {{ ELEMENT_NO_PARENT }}->getAttribute(\'attribute_name\')',
                ],
                'expectedCompilationMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT_NO_PARENT',
                    ])),
            ],
            'attribute value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))
                            ->withAttributeName('attribute_name')
                            ->withParentIdentifier(new DomIdentifier('.parent'))
                    ),
                    new VariablePlaceholder('ELEMENT_NO_PARENT')
                ),
                'expectedStatements' => [
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}'
                    .'->hasOne(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ ELEMENT_NO_PARENT }} = {{ DOM_CRAWLER_NAVIGATOR }}'
                    .'->findOne(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ ELEMENT_NO_PARENT }} = {{ ELEMENT_NO_PARENT }}->getAttribute(\'attribute_name\')',
                ],
                'expectedCompilationMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT_NO_PARENT',
                    ])),
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
