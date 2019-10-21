<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\CallFactory;

use Facebook\WebDriver\WebDriverElement;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
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
     * @dataProvider createFindCallDataProvider
     */
    public function testCreateFindCall(
        string $fixture,
        DomIdentifierInterface $identifier,
        callable $assertions
    ) {
        $source = $this->factory->createFindCall($identifier);

        $executableCall = $this->createExecutableCallWithReturn($source, $fixture);

        $element = eval($executableCall);

        $assertions($element);
    }

    public function createFindCallDataProvider(): array
    {
        return [
            'css selector, no parent' => [
                'fixture' => '/form.html',
                'identifier' => new DomIdentifier('input', 1),
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
                'identifier' => (new DomIdentifier('input'))
                    ->withParentIdentifier(new DomIdentifier('form[action="/action2"]')),
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
     * @dataProvider createHasCallDataProvider
     */
    public function testCreateHasCall(
        string $fixture,
        DomIdentifierInterface $identifier,
        bool $expectedHasElement
    ) {
        $source = $this->factory->createHasCall($identifier);

        $executableCall = $this->createExecutableCallWithReturn($source, $fixture);

        $this->assertSame($expectedHasElement, eval($executableCall));
    }

    public function createHasCallDataProvider(): array
    {
        return [
            'not hasElement: css selector only' => [
                'fixture' => '/index.html',
                'identifier' => new DomIdentifier('.non-existent'),
                'expectedHasElement' => false,
            ],
            'not hasElement: css selector with parent, parent does not exist' => [
                'fixture' => '/index.html',
                'identifier' => (new DomIdentifier('.non-existent-child', 1))
                    ->withParentIdentifier(new DomIdentifier('.non-existent-parent', 1)),
                'expectedHasElement' => false,
            ],
            'not hasElement: css selector with parent, child does not exist' => [
                'fixture' => '/form.html',
                'identifier' => (new DomIdentifier('.non-existent-child', 1))
                    ->withParentIdentifier(new DomIdentifier('form[action="/action1"]', 1)),
                'expectedHasElement' => false,
            ],
            'hasElement: css selector only' => [
                'fixture' => '/index.html',
                'identifier' => new DomIdentifier('h1', 1),
                'expectedHasElement' => true,
            ],
            'hasElement: css selector with parent' => [
                'fixture' => '/form.html',
                'identifier' => (new DomIdentifier('input', 1))
                    ->withParentIdentifier(new DomIdentifier('form[action="/action1"]', 1)),
                'expectedHasElement' => true,
            ],
        ];
    }
}
