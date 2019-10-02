<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\CallFactory;

use Facebook\WebDriver\WebDriverElement;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependency;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\DomElementLocator\ElementLocator;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
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

        $executableCall = $this->executableCallFactory->createWithReturn(
            $compilableSource,
            self::VARIABLE_IDENTIFIERS,
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                '$domCrawlerNavigator = Navigator::create($crawler); ',
            ],
            [],
            new ClassDependencyCollection([
                new ClassDependency(Navigator::class),
            ])
        );

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
    public function testCreateFindCallForTranspiledLocator(
        string $fixture,
        CompilableSourceInterface $arguments,
        callable $assertions
    ) {
        $compilableSource = $this->factory->createFindCallForTranspiledArguments($arguments);

        $executableCall = $this->executableCallFactory->createWithReturn(
            $compilableSource,
            self::VARIABLE_IDENTIFIERS,
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                '$domCrawlerNavigator = Navigator::create($crawler); ',
            ],
            [],
            new ClassDependencyCollection([
                new ClassDependency(Navigator::class),
            ])
        );
        $element = eval($executableCall);

        $assertions($element);
    }

    public function createFindCallForTranspiledArgumentsDataProvider(): array
    {
        return [
            'css selector, no parent' => [
                'fixture' => '/form.html',
                'arguments' => new CompilableSource(
                    ['new ElementLocator(\'input\', 1)'],
                    new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class)
                    ]),
                    new VariablePlaceholderCollection()
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
                'arguments' => new CompilableSource(
                    [
                        'new ElementLocator(\'input\', 1), ' .
                        'new ElementLocator(\'form[action="/action2"]\', 1)'
                    ],
                    new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class)
                    ]),
                    new VariablePlaceholderCollection()
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

        $executableCall = $this->executableCallFactory->createWithReturn(
            $compilableSource,
            self::VARIABLE_IDENTIFIERS,
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                '$domCrawlerNavigator = Navigator::create($crawler); ',
            ],
            [],
            new ClassDependencyCollection([
                new ClassDependency(Navigator::class),
            ])
        );

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

        $executableCall = $this->executableCallFactory->createWithReturn(
            $compilableSource,
            self::VARIABLE_IDENTIFIERS,
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                '$domCrawlerNavigator = Navigator::create($crawler); ',
            ],
            [],
            new ClassDependencyCollection([
                new ClassDependency(Navigator::class),
            ])
        );

        $this->assertSame($expectedHasElement, eval($executableCall));
    }

    public function createHasCallForTranspiledArgumentsDataProvider(): array
    {
        return [
            'not hasElement: css selector only' => [
                'fixture' => '/index.html',
                'arguments' => new CompilableSource(
                    ['new ElementLocator(\'.non-existent\', 1)'],
                    new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class)
                    ]),
                    new VariablePlaceholderCollection()
                ),
                'expectedHasElement' => false,
            ],
            'not hasElement: css selector with parent, parent does not exist' => [
                'fixture' => '/index.html',
                'arguments' => new CompilableSource(
                    [
                        'new ElementLocator(\'.non-existent-child\', 1), ' .
                        'new ElementLocator(\'.non-existent-parent\', 1)'
                    ],
                    new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class)
                    ]),
                    new VariablePlaceholderCollection()
                ),
                'expectedHasElement' => false,
            ],
            'not hasElement: css selector with parent, child does not exist' => [
                'fixture' => '/form.html',
                'arguments' => new CompilableSource(
                    [
                        'new ElementLocator(\'.non-existent-child\', 1), ' .
                        'new ElementLocator(\'form[action="/action1"]\', 1)'
                    ],
                    new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class)
                    ]),
                    new VariablePlaceholderCollection()
                ),
                'expectedHasElement' => false,
            ],
            'hasElement: css selector only' => [
                'fixture' => '/index.html',
                'arguments' => new CompilableSource(
                    ['new ElementLocator(\'h1\', 1)'],
                    new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class)
                    ]),
                    new VariablePlaceholderCollection()
                ),
                'expectedHasElement' => true,
            ],
            'hasElement: css selector with parent' => [
                'fixture' => '/form.html',
                'arguments' => new CompilableSource(
                    [
                        'new ElementLocator(\'input\', 1), ' .
                        'new ElementLocator(\'form[action="/action1"]\', 1)'
                    ],
                    new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class)
                    ]),
                    new VariablePlaceholderCollection()
                ),
                'expectedHasElement' => true,
            ],
        ];
    }
}
