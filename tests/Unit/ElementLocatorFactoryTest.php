<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit;

use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\ElementLocatorFactory;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;
use webignition\SymfonyDomCrawlerNavigator\Model\LocatorType;

class ElementLocatorFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ElementLocatorFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = ElementLocatorFactory::createFactory();
    }

    /**
     * @dataProvider createElementLocatorConstructorCallDataProvider
     */
    public function testCreateElementLocatorConstructorCall(
        ElementIdentifierInterface $elementIdentifier,
        ElementLocator $expectedElementLocator
    ) {
        $elementLocatorConstructorCall = $this->factory->createElementLocatorConstructorCall($elementIdentifier);

        $executableCall =
            'use ' . ElementLocator::class . ';' . "\n" .
            'use ' . LocatorType::class . ';' . "\n" .
            'return ' . $elementLocatorConstructorCall . ';'
        ;

        $elementLocator = eval($executableCall);

        $this->assertEquals($expectedElementLocator, $elementLocator);
    }

    public function createElementLocatorConstructorCallDataProvider(): array
    {
        return [
            'css selector, no quotes in selector, position default' => [
                'elementIdentifier' => new ElementIdentifier(
                    LiteralValue::createCssSelectorValue('.selector')
                ),
                'expectedElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    '.selector',
                    1
                ),
            ],
            'css selector, no quotes in selector, position 1' => [
                'elementIdentifier' => new ElementIdentifier(
                    LiteralValue::createCssSelectorValue('.selector'),
                    1
                ),
                'expectedElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    '.selector',
                    1
                ),
            ],
            'css selector, no quotes in selector, position 2' => [
                'elementIdentifier' => new ElementIdentifier(
                    LiteralValue::createCssSelectorValue('.selector'),
                    2
                ),
                'expectedElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    '.selector',
                    2
                ),
            ],
            'css selector, double quotes in selector, position default' => [
                'elementIdentifier' => new ElementIdentifier(
                    LiteralValue::createCssSelectorValue('input[name="email"]')
                ),
                'expectedElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    'input[name="email"]',
                    1
                ),
            ],
            'css selector, single quotes in selector, position default' => [
                'elementIdentifier' => new ElementIdentifier(
                    LiteralValue::createCssSelectorValue("input[name='email']")
                ),
                'expectedElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    "input[name='email']",
                    1
                ),
            ],
            'css selector, escaped single quotes in selector, position default' => [
                'elementIdentifier' => new ElementIdentifier(
                    LiteralValue::createCssSelectorValue("input[value='\'quoted\'']")
                ),
                'expectedElementLocator' => new ElementLocator(
                    LocatorType::CSS_SELECTOR,
                    "input[value='\'quoted\'']",
                    1
                ),
            ],
        ];
    }

    public function testCreateElementLocatorConstructorCallThrowsNonTranspilableModelException()
    {
        $value = new ObjectValue(ValueTypes::PAGE_ELEMENT_REFERENCE, '', '', '');

        $elementIdentifier = \Mockery::mock(ElementIdentifierInterface::class);
        $elementIdentifier
            ->shouldReceive('getValue')
            ->andReturn($value);

        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "' . get_class($elementIdentifier) . '"');

        $this->factory->createElementLocatorConstructorCall($elementIdentifier);
    }
}
