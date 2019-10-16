<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional;

use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilCompilationSource\CompilationMetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilTranspiler\Model\NamedDomIdentifier;
use webignition\BasilTranspiler\Model\NamedDomIdentifierInterface;
use webignition\BasilTranspiler\NamedDomIdentifierTranspiler;
use webignition\BasilTranspiler\Tests\DataProvider\Value\NamedDomIdentifierValueFunctionalDataProviderTrait;
use webignition\BasilTranspiler\VariableNames;
use webignition\DomElementLocator\ElementLocator;
use webignition\WebDriverElementInspector\Inspector;

class NamedDomIdentifierTranspilerTest extends AbstractTestCase
{
    use NamedDomIdentifierValueFunctionalDataProviderTrait;

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
     * @dataProvider namedDomIdentifierValueFunctionalDataProvider
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(
        string $fixture,
        NamedDomIdentifierInterface $namedDomIdentifier,
        CompilationMetadataInterface $expectedCompilationMetadata,
        $expectedExecutedResult,
        array $additionalVariableIdentifiers = [],
        array $additionalSetupStatements = [],
        ?CompilationMetadataInterface $additionalCompilationMetadata = null
    ) {
        $compilableSource = $this->transpiler->transpile($namedDomIdentifier);

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
            'element identifier, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifier(
                    new DomIdentifier('input', 1),
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
            'element identifier, has parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifier(
                    (new DomIdentifier('input', 1))
                        ->withParentIdentifier(new DomIdentifier('form[action="/action2"]')),
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
            'attribute identifier, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifier(
                    (new DomIdentifier('input', 1))->withAttributeName('name'),
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
            'attribute identifier, has parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifier(
                    (new DomIdentifier('input', 1))
                        ->withAttributeName('name')
                        ->withParentIdentifier(new DomIdentifier('form[action="/action2"]')),
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
