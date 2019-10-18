<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional;

use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilTranspiler\Model\NamedDomIdentifier;
use webignition\BasilTranspiler\Model\NamedDomIdentifierInterface;
use webignition\BasilTranspiler\Model\NamedDomIdentifierValue;
use webignition\BasilTranspiler\NamedDomIdentifierTranspiler;
use webignition\BasilTranspiler\VariableNames;
use webignition\DomElementLocator\ElementLocator;
use webignition\WebDriverElementCollection\WebDriverElementCollection;
use webignition\WebDriverElementInspector\Inspector;

class NamedDomIdentifierTranspilerTest extends AbstractTestCase
{
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
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(
        string $fixture,
        NamedDomIdentifierInterface $namedDomIdentifier,
        MetadataInterface $expectedMetadata,
        callable $resultAssertions,
        array $additionalVariableIdentifiers = [],
        array $additionalSetupStatements = [],
        ?MetadataInterface $additionalMetadata = null
    ) {
        $source = $this->transpiler->transpile($namedDomIdentifier);

        $this->assertEquals($expectedMetadata, $source->getMetadata());

        $executableCall = $this->createExecutableCallWithReturn(
            $source,
            $fixture,
            array_merge(
                self::VARIABLE_IDENTIFIERS,
                $additionalVariableIdentifiers
            ),
            $additionalSetupStatements,
            [],
            $additionalMetadata
        );

        $resultAssertions(eval($executableCall));
    }

    public function transpileDataProvider(): array
    {
        return [
            'element value, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        new DomIdentifier('input', 1)
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'expectedMetadata' => (new Metadata())
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
                'resultAssertions' => function ($result) {
                    $this->assertEquals('', $result);
                },
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$inspector',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalMetadata' => (new Metadata())
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
                'expectedMetadata' => (new Metadata())
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
                'resultAssertions' => function ($result) {
                    $this->assertEquals('test', $result);
                },
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$inspector',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalMetadata' => (new Metadata())
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
                'expectedMetadata' => (new Metadata())
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
                'resultAssertions' => function ($result) {
                    $this->assertEquals('input-without-value', $result);
                },
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalMetadata' => (new Metadata())
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
                'expectedMetadata' => (new Metadata())
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
                'resultAssertions' => function ($result) {
                    $this->assertEquals('input-2', $result);
                },
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
            'element identifier, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifier(
                    new DomIdentifier('input', 1),
                    new VariablePlaceholder('ELEMENT')
                ),
                'expectedMetadata' => (new Metadata())
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
                'resultAssertions' => function (WebDriverElementCollection $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->current();
                    $this->assertEquals('', $element->getAttribute('value'));
                },
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$inspector',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalMetadata' => (new Metadata())
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
                'expectedMetadata' => (new Metadata())
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
                'resultAssertions' => function (WebDriverElementCollection $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->current();
                    $this->assertEquals('', $element->getAttribute('test'));
                },
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                    'WEBDRIVER_ELEMENT_INSPECTOR' => '$inspector',
                ],
                'additionalSetupStatements' => [
                    '$inspector = new Inspector();',
                ],
                'additionalMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
        ];
    }
}
