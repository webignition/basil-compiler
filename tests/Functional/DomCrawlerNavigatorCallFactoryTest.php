<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional;

use Facebook\WebDriver\WebDriverElement;
use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Tests\Services\ExecutableCallFactory;
use webignition\BasilTranspiler\VariableNames;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;
use webignition\SymfonyDomCrawlerNavigator\Model\LocatorType;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

class DomCrawlerNavigatorCallFactoryTest extends AbstractTestCase
{
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
     * @dataProvider createFindElementCallForIdentifierDataProvider
     */
    public function testCreateFindElementCallForIdentifier(
        string $fixture,
        ElementIdentifierInterface $elementIdentifier,
        callable $assertions
    ) {
        $variableIdentifiers = [
            VariableNames::DOM_CRAWLER_NAVIGATOR => '$domCrawlerNavigator',
        ];

        $transpilationResult = $this->factory->createFindElementCallForIdentifier(
            $elementIdentifier,
            $variableIdentifiers
        );

        $executableCall = $this->executableCallFactory->create(
            $transpilationResult,
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

    public function createFindElementCallForIdentifierDataProvider(): array
    {
        return [
            'css selector, no parent' => [
                'fixture' => '/basic.html',
                'elementIdentifier' => TestIdentifierFactory::createCssElementIdentifier('input'),
                'assertions' => function (WebDriverElement $element) {
                    $this->assertSame('input-1', $element->getAttribute('name'));
                },
            ],
            'css selector, has parent' => [
                'fixture' => '/basic.html',
                'elementIdentifier' => TestIdentifierFactory::createCssElementIdentifier(
                    'input',
                    1,
                    null,
                    TestIdentifierFactory::createCssElementIdentifier('form[action="/action2"]')
                ),
                'assertions' => function (WebDriverElement $element) {
                    $this->assertSame('input-2', $element->getAttribute('name'));
                },
            ],
        ];
    }

    /**
     * @dataProvider createFindElementCallForTranspiledArgumentsDataProvider
     */
    public function testCreateFindElementCallForTranspiledLocator(
        string $fixture,
        TranspilationResult $arguments,
        callable $assertions
    ) {
        $variableIdentifiers = [
            VariableNames::DOM_CRAWLER_NAVIGATOR => '$domCrawlerNavigator',
        ];

        $transpilationResult = $this->factory->createFindElementCallForTranspiledArguments(
            $arguments,
            $variableIdentifiers
        );

        $executableCall = $this->executableCallFactory->create(
            $transpilationResult,
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

    public function createFindElementCallForTranspiledArgumentsDataProvider(): array
    {
        return [
            'css selector, no parent' => [
                'fixture' => '/basic.html',
                'arguments' => new TranspilationResult(
                    'new ElementLocator(LocatorType::CSS_SELECTOR, \'input\', 1)',
                    new UseStatementCollection([
                        new UseStatement(LocatorType::class),
                        new UseStatement(ElementLocator::class)
                    ])
                ),
                'assertions' => function (WebDriverElement $element) {
                    $this->assertSame('input-1', $element->getAttribute('name'));
                },
            ],
            'css selector, has parent' => [
                'fixture' => '/basic.html',
                'arguments' => new TranspilationResult(
                    'new ElementLocator(LocatorType::CSS_SELECTOR, \'input\', 1), ' .
                    'new ElementLocator(LocatorType::CSS_SELECTOR, \'form[action="/action2"]\', 1)',
                    new UseStatementCollection([
                        new UseStatement(LocatorType::class),
                        new UseStatement(ElementLocator::class)
                    ])
                ),
                'assertions' => function (WebDriverElement $element) {
                    $this->assertSame('input-2', $element->getAttribute('name'));
                },
            ],
        ];
    }

    /**
     * @dataProvider createHasElementCallDataProvider
     */
    public function testCreateHasElementCall(
        string $fixture,
        ElementIdentifierInterface $elementIdentifier,
        bool $expectedHasElement
    ) {
        $variableIdentifiers = [
            VariableNames::DOM_CRAWLER_NAVIGATOR => '$domCrawlerNavigator',
        ];

        $transpilationResult = $this->factory->createHasElementCall($elementIdentifier, $variableIdentifiers);

        $executableCall = $this->executableCallFactory->create(
            $transpilationResult,
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

    public function createHasElementCallDataProvider(): array
    {
        return [
            'not hasElement: css selector, no parent' => [
                'fixture' => '/basic.html',
                'elementIdentifier' => TestIdentifierFactory::createCssElementIdentifier('.selector'),
                'expectedHasElement' => false,
            ],
            'not hasElement: css selector, has parent' => [
                'fixture' => '/basic.html',
                'elementIdentifier' => TestIdentifierFactory::createCssElementIdentifier(
                    '.selector',
                    1,
                    null,
                    TestIdentifierFactory::createCssElementIdentifier('.parent')
                ),
                'expectedHasElement' => false,
            ],
            'hasElement: css selector, no parent' => [
                'fixture' => '/basic.html',
                'elementIdentifier' => TestIdentifierFactory::createCssElementIdentifier('h1'),
                'expectedHasElement' => true,
            ],
            'hasElement: css selector, has parent' => [
                'fixture' => '/basic.html',
                'elementIdentifier' => TestIdentifierFactory::createCssElementIdentifier(
                    'input',
                    1,
                    null,
                    TestIdentifierFactory::createCssElementIdentifier('form[action="/action2"]')
                ),
                'expectedHasElement' => true,
            ],
        ];
    }
}
