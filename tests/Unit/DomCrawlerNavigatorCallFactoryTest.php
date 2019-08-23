<?php
/** @noinspection PhpRedundantCatchClauseInspection */
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit;

use Facebook\WebDriver\WebDriver;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\UseStatementTranspiler;
use webignition\BasilTranspiler\VariableNames;
use webignition\SymfonyDomCrawlerNavigator\Exception\UnknownElementException;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;
use webignition\SymfonyDomCrawlerNavigator\Model\LocatorType;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

class DomCrawlerNavigatorCallFactoryTest extends \PHPUnit\Framework\TestCase
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
        ElementIdentifierInterface $elementIdentifier,
        ElementLocator $expectedExceptionElementLocator
    ) {
        $variableIdentifiers = [
            VariableNames::DOM_CRAWLER_NAVIGATOR => '$domCrawlerNavigator',
        ];

        $transpilationResult = $this->factory->createFindElementCall($elementIdentifier, $variableIdentifiers);

        $executableCall = '';

        foreach ($transpilationResult->getUseStatements() as $key => $value) {
            $executableCall .= (string) $this->useStatementTranspiler->transpile($value) . ";\n";
        }

        $executableCall .=
            'use ' . Crawler::class . ';' . "\n" .
            'use ' . WebDriver::class . ';' . "\n" .
            'use ' . Navigator::class . ';' . "\n" .
            '$crawler = new Crawler([], \Mockery::mock(WebDriver::class)); ' . "\n" .
            '$domCrawlerNavigator = Navigator::create($crawler); ' . "\n" .
            'return ' . (string) $transpilationResult . ';'
        ;

        try {
            eval($executableCall);
            $this->fail('UnknownElementException not thrown when executing Navigator::findElement()');
        } catch (UnknownElementException $unknownElementException) {
            $this->assertEquals($expectedExceptionElementLocator, $unknownElementException->getElementLocator());
        }
    }

    public function createFindElementCallDataProvider(): array
    {
        return [
            'css selector, no parent' => [
                'elementIdentifier' => TestIdentifierFactory::createCssElementIdentifier('.selector'),
                'expectedExceptionElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    '.selector',
                    1
                ),
            ],
            'css selector, has parent' => [
                'elementIdentifier' => TestIdentifierFactory::createCssElementIdentifier(
                    '.selector',
                    1,
                    null,
                    TestIdentifierFactory::createCssElementIdentifier('.parent')
                ),
                'expectedExceptionElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    '.parent',
                    1
                ),
            ],
        ];
    }
}
