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
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\Tests\Services\ExecutableCallFactory;
use webignition\BasilTranspiler\VariableNames;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

class IdentifierTranspilerTest extends AbstractTestCase
{
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
        $variableIdentifiers = [
            VariableNames::DOM_CRAWLER_NAVIGATOR => '$domCrawlerNavigator',
        ];

        $transpilationResult = $this->transpiler->transpile($identifier, $variableIdentifiers);

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
