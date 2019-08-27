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
use webignition\BasilTranspiler\UseStatementTranspiler;
use webignition\BasilTranspiler\VariableNames;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

class DomCrawlerNavigatorCallFactoryTest extends AbstractTestCase
{
    /**
     * @var DomCrawlerNavigatorCallFactory
     */
    private $factory;

    /**
     * @var UseStatementTranspiler
     */
    private $useStatementTranspiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
        $this->useStatementTranspiler = UseStatementTranspiler::createTranspiler();
    }

    /**
     * @dataProvider createFindElementCallDataProvider
     */
    public function testCreateFindElementCall(
        string $fixture,
        ElementIdentifierInterface $elementIdentifier,
        callable $assertions
    ) {
        $variableIdentifiers = [
            VariableNames::DOM_CRAWLER_NAVIGATOR => '$domCrawlerNavigator',
        ];

        $transpilationResult = $this->factory->createFindElementCall($elementIdentifier, $variableIdentifiers);

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                '$domCrawlerNavigator = Navigator::create($crawler); ',
            ]
        );
        $element = eval($executableCall);

        $assertions($element);
    }

    public function createFindElementCallDataProvider(): array
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

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                '$domCrawlerNavigator = Navigator::create($crawler); ',
            ]
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

    private function createExecutableCall(TranspilationResult $transpilationResult, array $setUp = []): string
    {
        $useStatements = $transpilationResult->getUseStatements();
        $useStatements = $useStatements->withAdditionalUseStatements(new UseStatementCollection([
            new UseStatement(Navigator::class),
        ]));

        $executableCall = '';

        foreach ($useStatements as $key => $value) {
            $executableCall .= (string) $this->useStatementTranspiler->transpile($value) . ";\n";
        }

        foreach ($setUp as $line) {
            $executableCall .= $line . "\n";
        }

        $executableCall .= 'return ' . (string) $transpilationResult . ';';

        return $executableCall;
    }
}
