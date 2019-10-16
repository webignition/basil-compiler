<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Value;

use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilCompilationSource\CompilationMetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;

use webignition\BasilTranspiler\Model\NamedDomIdentifierValue;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\Value\ValueTranspiler;
use webignition\BasilTranspiler\VariableNames;
use webignition\DomElementLocator\ElementLocator;
use webignition\WebDriverElementInspector\Inspector;

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
        array $additionalVariableIdentifiers = [],
        array $additionalSetupStatements = [],
        ?CompilationMetadataInterface $additionalCompilationMetadata = null
    ) {
        $compilableSource = $this->transpiler->transpile($model);

        $this->assertEquals($expectedCompilationMetadata, $compilableSource->getCompilationMetadata());

        $executableCall = $this->createExecutableCallWithReturn(
            $compilableSource,
            $fixture,
            array_merge(
                self::VARIABLE_IDENTIFIERS,
                $additionalVariableIdentifiers
            ),
            $additionalSetupStatements,
            [],
            $additionalCompilationMetadata
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
            'element value, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        new DomIdentifier('input', 1)
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'expectedCompilationMetadata' => (new CompilationMetadata())
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
                        'ELEMENT',
                    ])),
                'expectedExecutedResult' => '',
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$inspector',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
            'element value, has parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('input', 1))
                            ->withParentIdentifier(new DomIdentifier('form[action="/action2"]'))
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'expectedCompilationMetadata' => (new CompilationMetadata())
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
                        'ELEMENT',
                    ])),
                'expectedExecutedResult' => 'test',
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$inspector',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
            'attribute value, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('input', 1))->withAttributeName('name')
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'expectedCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT',
                    ])),
                'expectedExecutedResult' => 'input-without-value',
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
            'attribute value, has parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('input', 1))
                            ->withAttributeName('name')
                            ->withParentIdentifier(new DomIdentifier('form[action="/action2"]'))
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'expectedCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT',
                    ])),
                'expectedExecutedResult' => 'input-2',
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalCompilationMetadata' => (new CompilationMetadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
        ];
    }
}
