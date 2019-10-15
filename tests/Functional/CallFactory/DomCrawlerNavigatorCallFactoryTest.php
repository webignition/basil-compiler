<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\CallFactory;

use Facebook\WebDriver\WebDriverElement;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\DomElementLocator\ElementLocator;
use webignition\WebDriverElementCollection\WebDriverElementCollectionInterface;

class DomCrawlerNavigatorCallFactoryTest extends AbstractTestCase
{
    /**
     * @var DomCrawlerNavigatorCallFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
    }

    /**
     * @dataProvider createFindCallForIdentifierDataProvider
     */
    public function testCreateFindCallForIdentifier(
        string $fixture,
        DomIdentifierInterface $elementIdentifier,
        callable $assertions
    ) {
        $compilableSource = $this->factory->createFindCallForIdentifier($elementIdentifier);

        $executableCall = $this->createExecutableCallWithReturn($compilableSource, $fixture);

        $element = eval($executableCall);

        $assertions($element);
    }

    public function createFindCallForIdentifierDataProvider(): array
    {
        return [
            'css selector, no parent' => [
                'fixture' => '/form.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier('input[name=input-with-value]'),
                'assertions' => function (WebDriverElementCollectionInterface $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    if ($element instanceof WebDriverElement) {
                        $this->assertSame('input-with-value', $element->getAttribute('name'));
                    }
                },
            ],
            'css selector, has parent' => [
                'fixture' => '/form.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier(
                    'input',
                    1,
                    null,
                    TestIdentifierFactory::createElementIdentifier('form[action="/action2"]')
                ),
                'assertions' => function (WebDriverElementCollectionInterface $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    if ($element instanceof WebDriverElement) {
                        $this->assertSame('input-2', $element->getAttribute('name'));
                    }
                },
            ],
        ];
    }

    /**
     * @dataProvider createFindCallForTranspiledArgumentsDataProvider
     */
    public function testCreateFindCallForTranspiledArguments(
        string $fixture,
        CompilableSourceInterface $arguments,
        callable $assertions
    ) {
        $compilableSource = $this->factory->createFindCallForTranspiledArguments($arguments);

        $executableCall = $this->createExecutableCallWithReturn($compilableSource, $fixture);

        $element = eval($executableCall);

        $assertions($element);
    }

    public function createFindCallForTranspiledArgumentsDataProvider(): array
    {
        return [
            'css selector, no parent' => [
                'fixture' => '/form.html',
                'arguments' => (new CompilableSource())
                    ->withStatements(['new ElementLocator(\'input\', 1)'])
                    ->withCompilationMetadata(
                        (new CompilationMetadata())->withClassDependencies(new ClassDependencyCollection([
                            new ClassDependency(ElementLocator::class)
                        ]))
                    ),
                'assertions' => function (WebDriverElementCollectionInterface $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    if ($element instanceof WebDriverElement) {
                        $this->assertSame('input-without-value', $element->getAttribute('name'));
                    }
                },
            ],
            'css selector, has parent' => [
                'fixture' => '/form.html',
                'arguments' => (new CompilableSource())
                    ->withStatements([
                        'new ElementLocator(\'input\', 1), ' .
                        'new ElementLocator(\'form[action="/action2"]\', 1)'
                    ])
                    ->withCompilationMetadata(
                        (new CompilationMetadata())->withClassDependencies(new ClassDependencyCollection([
                            new ClassDependency(ElementLocator::class)
                        ]))
                    ),
                'assertions' => function (WebDriverElementCollectionInterface $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    if ($element instanceof WebDriverElement) {
                        $this->assertSame('input-2', $element->getAttribute('name'));
                    }
                },
            ],
        ];
    }

    /**
     * @dataProvider createHasCallForIdentifierDataProvider
     */
    public function testCreateHasCallForIdentifier(
        string $fixture,
        DomIdentifierInterface $elementIdentifier,
        bool $expectedHasElement
    ) {
        $compilableSource = $this->factory->createHasCallForIdentifier($elementIdentifier);

        $executableCall = $this->createExecutableCallWithReturn($compilableSource, $fixture);

        $this->assertSame($expectedHasElement, eval($executableCall));
    }

    public function createHasCallForIdentifierDataProvider(): array
    {
        return [
            'not hasElement: css selector only' => [
                'fixture' => '/index.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier('.non-existent'),
                'expectedHasElement' => false,
            ],
            'not hasElement: css selector with parent, neither exist' => [
                'fixture' => '/index.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier(
                    '.non-existent-child',
                    1,
                    null,
                    TestIdentifierFactory::createElementIdentifier('.non-existent-parent')
                ),
                'expectedHasElement' => false,
            ],
            'not hasElement: css selector with parent, parent does not exist' => [
                'fixture' => '/index.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier(
                    '.non-existent-child',
                    1,
                    null,
                    TestIdentifierFactory::createElementIdentifier('.non-existent-parent')
                ),
                'expectedHasElement' => false,
            ],
            'not hasElement: css selector with parent, child does not exist' => [
                'fixture' => '/form.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier(
                    '.non-existent-child',
                    1,
                    null,
                    TestIdentifierFactory::createElementIdentifier('form[action="/action1"]')
                ),
                'expectedHasElement' => false,
            ],
            'hasElement: css selector' => [
                'fixture' => '/index.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier('h1'),
                'expectedHasElement' => true,
            ],
            'hasElement: css selector with parent' => [
                'fixture' => '/form.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier(
                    'input',
                    1,
                    null,
                    TestIdentifierFactory::createElementIdentifier('form[action="/action1"]')
                ),
                'expectedHasElement' => true,
            ],
        ];
    }

    /**
     * @dataProvider createHasCallForTranspiledArgumentsDataProvider
     */
    public function testCreateHasCallForTranspiledArguments(
        string $fixture,
        CompilableSourceInterface $arguments,
        bool $expectedHasElement
    ) {
        $compilableSource = $this->factory->createHasCallForTranspiledArguments($arguments);

        $executableCall = $this->createExecutableCallWithReturn($compilableSource, $fixture);

        $this->assertSame($expectedHasElement, eval($executableCall));
    }

    public function createHasCallForTranspiledArgumentsDataProvider(): array
    {
        $expectedCompilationMetadata = (new CompilationMetadata())
            ->withClassDependencies(new ClassDependencyCollection([
                new ClassDependency(ElementLocator::class)
            ]));

        return [
            'not hasElement: css selector only' => [
                'fixture' => '/index.html',
                'arguments' => (new CompilableSource())
                    ->withStatements([
                        'new ElementLocator(\'.non-existent\', 1)'
                    ])
                    ->withCompilationMetadata($expectedCompilationMetadata),
                'expectedHasElement' => false,
            ],
            'not hasElement: css selector with parent, parent does not exist' => [
                'fixture' => '/index.html',
                'arguments' => (new CompilableSource())
                    ->withStatements([
                        'new ElementLocator(\'.non-existent-child\', 1), ' .
                        'new ElementLocator(\'.non-existent-parent\', 1)'
                    ])
                    ->withCompilationMetadata($expectedCompilationMetadata),
                'expectedHasElement' => false,
            ],
            'not hasElement: css selector with parent, child does not exist' => [
                'fixture' => '/form.html',
                'arguments' => (new CompilableSource())
                    ->withStatements([
                        'new ElementLocator(\'.non-existent-child\', 1), ' .
                        'new ElementLocator(\'form[action="/action1"]\', 1)'
                    ])
                    ->withCompilationMetadata($expectedCompilationMetadata),
                'expectedHasElement' => false,
            ],
            'hasElement: css selector only' => [
                'fixture' => '/index.html',
                'arguments' => (new CompilableSource())
                    ->withStatements([
                        'new ElementLocator(\'h1\', 1)'
                    ])
                    ->withCompilationMetadata($expectedCompilationMetadata),
                'expectedHasElement' => true,
            ],
            'hasElement: css selector with parent' => [
                'fixture' => '/form.html',
                'arguments' => (new CompilableSource())
                    ->withStatements([
                        'new ElementLocator(\'input\', 1), ' .
                        'new ElementLocator(\'form[action="/action1"]\', 1)'
                    ])
                    ->withCompilationMetadata($expectedCompilationMetadata),
                'expectedHasElement' => true,
            ],
        ];
    }
}
