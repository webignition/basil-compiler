<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\CallFactory;

use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;
use webignition\BasilTranspiler\CallFactory\ElementLocatorCallFactory;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Tests\Services\ExecutableCallFactory;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;
use webignition\SymfonyDomCrawlerNavigator\Model\LocatorType;

class ElementLocatorCallFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ElementLocatorCallFactory
     */
    private $factory;

    /**
     * @var ExecutableCallFactory
     */
    private $executableCallFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = ElementLocatorCallFactory::createFactory();
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    /**
     * @dataProvider createConstructorCallDataProvider
     */
    public function testCreateConstructorCall(
        DomIdentifierInterface $elementIdentifier,
        ElementLocator $expectedElementLocator
    ) {
        $transpilationResult = $this->factory->createConstructorCall($elementIdentifier);

        $this->assertEquals(
            [
                new UseStatement(ElementLocator::class),
                new UseStatement(LocatorType::class),
            ],
            $transpilationResult->getUseStatements()->getAll()
        );

        $this->assertEquals([], $transpilationResult->getVariablePlaceholders()->getAll());

        $executableCall = $this->executableCallFactory->createWithReturn($transpilationResult);
        $elementLocator = eval($executableCall);

        $this->assertEquals($expectedElementLocator, $elementLocator);
    }

    public function createConstructorCallDataProvider(): array
    {
        $cssSelectorElementExpression = new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR);

        return [
            'css selector, no quotes in selector, position default' => [
                'elementIdentifier' => new DomIdentifier($cssSelectorElementExpression),
                'expectedElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    '.selector',
                    null
                ),
            ],
            'css selector, no quotes in selector, position 1' => [
                'elementIdentifier' => (new DomIdentifier($cssSelectorElementExpression))->withPosition(1),
                'expectedElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    '.selector',
                    1
                ),
            ],
            'css selector, no quotes in selector, position 2' => [
                'elementIdentifier' => (new DomIdentifier($cssSelectorElementExpression))->withPosition(2),
                'expectedElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    '.selector',
                    2
                ),
            ],
            'css selector, double quotes in selector, position default' => [
                'elementIdentifier' => new DomIdentifier(
                    new ElementExpression('input[name="email"]', ElementExpressionType::CSS_SELECTOR)
                ),
                'expectedElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    'input[name="email"]',
                    null
                ),
            ],
            'css selector, single quotes in selector, position default' => [
                'elementIdentifier' => new DomIdentifier(
                    new ElementExpression("input[name='email']", ElementExpressionType::CSS_SELECTOR)
                ),
                'expectedElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    "input[name='email']",
                    null
                ),
            ],
            'css selector, escaped single quotes in selector, position default' => [
                'elementIdentifier' => new DomIdentifier(
                    new ElementExpression("input[value='\'quoted\'']", ElementExpressionType::CSS_SELECTOR)
                ),
                'expectedElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    "input[value='\'quoted\'']",
                    null
                ),
            ],
        ];
    }
}
