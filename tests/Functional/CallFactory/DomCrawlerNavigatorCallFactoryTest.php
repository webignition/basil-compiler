<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\CallFactory;

use Facebook\WebDriver\WebDriverElement;
use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\CssSelector;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\Tests\Services\ExecutableCallFactory;
use webignition\BasilTranspiler\VariableNames;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;
use webignition\SymfonyDomCrawlerNavigator\Model\LocatorType;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementCollection\WebDriverElementCollectionInterface;

class DomCrawlerNavigatorCallFactoryTest extends AbstractTestCase
{
    const DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME = '$domCrawlerNavigator';
    const VARIABLE_IDENTIFIERS = [
        VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
    ];

    /**
     * @var DomCrawlerNavigatorCallFactory
     */
    private $factory;

    /**
     * @var ExecutableCallFactory
     */
    private $executableCallFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    /**
     * @dataProvider createFindCallForIdentifierDataProvider
     */
    public function testCreateFindCallForIdentifier(
        string $fixture,
        ElementIdentifierInterface $elementIdentifier,
        callable $assertions
    ) {
        $transpilationResult = $this->factory->createFindCallForIdentifier($elementIdentifier);

        $executableCall = $this->executableCallFactory->createWithReturn(
            $transpilationResult,
            self::VARIABLE_IDENTIFIERS,
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                '$domCrawlerNavigator = Navigator::create($crawler); ',
            ],
            new UseStatementCollection([
                new UseStatement(Navigator::class),
            ])
        );

        $element = eval($executableCall);

        $assertions($element);
    }

    public function createFindCallForIdentifierDataProvider(): array
    {
        return [
            'css selector, no parent' => [
                'fixture' => '/basic.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier(
                    new CssSelector('input[name="input-1"]')
                ),
                'assertions' => function (WebDriverElementCollectionInterface $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    if ($element instanceof WebDriverElement) {
                        $this->assertSame('input-1', $element->getAttribute('name'));
                    }
                },
            ],
            'css selector, has parent' => [
                'fixture' => '/basic.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier(
                    new CssSelector('input'),
                    1,
                    null,
                    TestIdentifierFactory::createElementIdentifier(new CssSelector('form[action="/action2"]'))
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
        TranspilationResultInterface $arguments,
        callable $assertions
    ) {
        $transpilationResult = $this->factory->createFindCallForTranspiledArguments($arguments);

        $executableCall = $this->executableCallFactory->createWithReturn(
            $transpilationResult,
            self::VARIABLE_IDENTIFIERS,
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                '$domCrawlerNavigator = Navigator::create($crawler); ',
            ],
            new UseStatementCollection([
                new UseStatement(Navigator::class),
            ])
        );
        $element = eval($executableCall);

        $assertions($element);
    }

    public function createFindCallForTranspiledArgumentsDataProvider(): array
    {
        return [
            'css selector, no parent' => [
                'fixture' => '/basic.html',
                'arguments' => new TranspilationResult(
                    ['new ElementLocator(LocatorType::CSS_SELECTOR, \'input\', 1)'],
                    new UseStatementCollection([
                        new UseStatement(LocatorType::class),
                        new UseStatement(ElementLocator::class)
                    ]),
                    new VariablePlaceholderCollection()
                ),
                'assertions' => function (WebDriverElementCollectionInterface $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    if ($element instanceof WebDriverElement) {
                        $this->assertSame('input-1', $element->getAttribute('name'));
                    }
                },
            ],
            'css selector, has parent' => [
                'fixture' => '/basic.html',
                'arguments' => new TranspilationResult(
                    [
                        'new ElementLocator(LocatorType::CSS_SELECTOR, \'input\', 1), ' .
                        'new ElementLocator(LocatorType::CSS_SELECTOR, \'form[action="/action2"]\', 1)'
                    ],
                    new UseStatementCollection([
                        new UseStatement(LocatorType::class),
                        new UseStatement(ElementLocator::class)
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
        ElementIdentifierInterface $elementIdentifier,
        bool $expectedHasElement
    ) {
        $transpilationResult = $this->factory->createHasCallForIdentifier($elementIdentifier);

        $executableCall = $this->executableCallFactory->createWithReturn(
            $transpilationResult,
            self::VARIABLE_IDENTIFIERS,
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                '$domCrawlerNavigator = Navigator::create($crawler); ',
            ],
            new UseStatementCollection([
                new UseStatement(Navigator::class),
            ])
        );

        $this->assertSame($expectedHasElement, eval($executableCall));
    }

    public function createHasCallForIdentifierDataProvider(): array
    {
        return [
            'not hasElement: css selector, no parent' => [
                'fixture' => '/basic.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier(new CssSelector('.selector')),
                'expectedHasElement' => false,
            ],
            'not hasElement: css selector, has parent' => [
                'fixture' => '/basic.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier(
                    new CssSelector('.selector'),
                    1,
                    null,
                    TestIdentifierFactory::createElementIdentifier(new CssSelector('.parent'))
                ),
                'expectedHasElement' => false,
            ],
            'hasElement: css selector, no parent' => [
                'fixture' => '/basic.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier(new CssSelector('h1')),
                'expectedHasElement' => true,
            ],
            'hasElement: css selector, has parent' => [
                'fixture' => '/basic.html',
                'elementIdentifier' => TestIdentifierFactory::createElementIdentifier(
                    new CssSelector('input'),
                    1,
                    null,
                    TestIdentifierFactory::createElementIdentifier(new CssSelector('form[action="/action2"]'))
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
        TranspilationResultInterface $arguments,
        bool $expectedHasElement
    ) {
        $transpilationResult = $this->factory->createHasCallForTranspiledArguments($arguments);

        $executableCall = $this->executableCallFactory->createWithReturn(
            $transpilationResult,
            self::VARIABLE_IDENTIFIERS,
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                '$domCrawlerNavigator = Navigator::create($crawler); ',
            ],
            new UseStatementCollection([
                new UseStatement(Navigator::class),
            ])
        );

        $this->assertSame($expectedHasElement, eval($executableCall));
    }

    public function createHasCallForTranspiledArgumentsDataProvider(): array
    {
        return [
            'not hasElement: css selector, no parent' => [
                'fixture' => '/basic.html',
                'arguments' => new TranspilationResult(
                    ['new ElementLocator(LocatorType::CSS_SELECTOR, \'.selector\', 1)'],
                    new UseStatementCollection([
                        new UseStatement(LocatorType::class),
                        new UseStatement(ElementLocator::class)
                    ]),
                    new VariablePlaceholderCollection()
                ),
                'expectedHasElement' => false,
            ],
            'not hasElement: css selector, has parent' => [
                'fixture' => '/basic.html',
                'arguments' => new TranspilationResult(
                    [
                        'new ElementLocator(LocatorType::CSS_SELECTOR, \'.selector\', 1), ' .
                        'new ElementLocator(LocatorType::CSS_SELECTOR, \'.parent\', 1)'
                    ],
                    new UseStatementCollection([
                        new UseStatement(LocatorType::class),
                        new UseStatement(ElementLocator::class)
                    ]),
                    new VariablePlaceholderCollection()
                ),
                'expectedHasElement' => false,
            ],
            'hasElement: css selector, no parent' => [
                'fixture' => '/basic.html',
                'arguments' => new TranspilationResult(
                    ['new ElementLocator(LocatorType::CSS_SELECTOR, \'h1\', 1)'],
                    new UseStatementCollection([
                        new UseStatement(LocatorType::class),
                        new UseStatement(ElementLocator::class)
                    ]),
                    new VariablePlaceholderCollection()
                ),
                'expectedHasElement' => true,
            ],
            'hasElement: css selector, has parent' => [
                'fixture' => '/basic.html',
                'arguments' => new TranspilationResult(
                    [
                        'new ElementLocator(LocatorType::CSS_SELECTOR, \'input\', 1), ' .
                        'new ElementLocator(LocatorType::CSS_SELECTOR, \'form[action="/action2"]\', 1)'
                    ],
                    new UseStatementCollection([
                        new UseStatement(LocatorType::class),
                        new UseStatement(ElementLocator::class)
                    ]),
                    new VariablePlaceholderCollection()
                ),
                'expectedHasElement' => true,
            ],
        ];
    }
}
