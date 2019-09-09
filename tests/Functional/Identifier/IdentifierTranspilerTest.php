<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Identifier;

use Facebook\WebDriver\WebDriverElement;
use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\Identifier\IdentifierTranspiler;
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

class IdentifierTranspilerTest extends AbstractTestCase
{
    const DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME = '$domCrawlerNavigator';
    const VARIABLE_IDENTIFIERS = [
        VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
    ];

    /**
     * @var IdentifierTranspiler
     */
    private $transpiler;

    /**
     * @var ExecutableCallFactory
     */
    private $executableCallFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = IdentifierTranspiler::createTranspiler();
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    /**
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(
        string $fixture,
        IdentifierInterface $identifier,
        array $variableIdentifiers,
        VariablePlaceholderCollection $expectedVariablePlaceholders,
        callable $assertions
    ) {
        $transpilationResult = $this->transpiler->transpile($identifier);

        $expectedUseStatements = new UseStatementCollection([
            new UseStatement(ElementLocator::class),
            new UseStatement(LocatorType::class),
        ]);

        $this->assertEquals($expectedUseStatements, $transpilationResult->getUseStatements());
        $this->assertEquals($expectedVariablePlaceholders, $transpilationResult->getVariablePlaceholders());

        $executableCall = $this->executableCallFactory->createWithReturn(
            $transpilationResult,
            $variableIdentifiers,
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                '$domCrawlerNavigator = Navigator::create($crawler); ',
            ],
            new UseStatementCollection([
                new UseStatement(Navigator::class),
            ])
        );

        $assertions(eval($executableCall));
    }

    public function transpileDataProvider(): array
    {
        return [
            'element identifier (css selector), selector only' => [
                'fixture' => '/basic.html',
                'identifier' => TestIdentifierFactory::createCssElementIdentifier('.p-1'),
                'variableIdentifiers' => array_merge(self::VARIABLE_IDENTIFIERS, [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'COLLECTION' => '$collection',
                    'PHPUNIT_TEST_CASE' => '$this',
                    'HAS' => '$has',
                ]),
                'expectedVariablePlaceholders' => VariablePlaceholderCollection::createCollection([
                    'ELEMENT_LOCATOR',
                    'COLLECTION',
                    'HAS',
                    VariableNames::DOM_CRAWLER_NAVIGATOR,
                    'PHPUNIT_TEST_CASE',
                ]),
                'assertions' => function (WebDriverElementCollectionInterface $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    if ($element instanceof WebDriverElement) {
                        $this->assertSame('P1', $element->getText());
                    }
                },
            ],
            'attribute identifier, selector only' => [
                'fixture' => '/basic.html',
                'identifier' => new AttributeIdentifier(
                    new ElementIdentifier(
                        LiteralValue::createCssSelectorValue('.foo')
                    ),
                    'id'
                ),
                'variableIdentifiers' => array_merge(self::VARIABLE_IDENTIFIERS, [
                    'ATTRIBUTE' => '$attribute',
                    'ELEMENT' => '$element',
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'COLLECTION' => '$collection',
                    'PHPUNIT_TEST_CASE' => '$this',
                    'HAS' => '$has',
                ]),
                'expectedVariablePlaceholders' => VariablePlaceholderCollection::createCollection([
                    'ATTRIBUTE',
                    'ELEMENT_LOCATOR',
                    'ELEMENT',
                    'HAS',
                    VariableNames::DOM_CRAWLER_NAVIGATOR,
                    'PHPUNIT_TEST_CASE',
                ]),
                'assertions' => function ($value) {
                    $this->assertIsString($value);
                    $this->assertSame('a-sibling', $value);
                },
            ],
        ];
    }
}
