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
        callable $assertions
    ) {
        $transpilationResult = $this->transpiler->transpile($identifier);

        $expectedUseStatements = new UseStatementCollection([
            new UseStatement(ElementLocator::class),
            new UseStatement(LocatorType::class),
        ]);

        $this->assertEquals($expectedUseStatements, $transpilationResult->getUseStatements());

        $expectedVariablePlaceholders = VariablePlaceholderCollection::createCollection([
            'ELEMENT_LOCATOR',
            'ELEMENT',
            'COLLECTION',
            VariableNames::DOM_CRAWLER_NAVIGATOR,
            'PHPUNIT_TEST_CASE',
        ]);

        $this->assertEquals($expectedVariablePlaceholders, $transpilationResult->getVariablePlaceholders());

        $executableCall = $this->executableCallFactory->createWithReturn(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, [
                'ELEMENT_LOCATOR' => '$elementLocator',
                'ELEMENT' => '$element',
                'COLLECTION' => '$collection',
                'PHPUNIT_TEST_CASE' => '$this',
            ]),
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
                'assertions' => function (WebDriverElement $element) {
                    $this->assertSame('P1', $element->getText());
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
                'assertions' => function ($value) {
                    $this->assertIsString($value);
                    $this->assertSame('a-sibling', $value);
                },
            ],
        ];
    }
}
