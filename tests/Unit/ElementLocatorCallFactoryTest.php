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
use webignition\BasilTranspiler\ElementLocatorCallFactory;
use webignition\BasilTranspiler\NonTranspilableModelException;
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
        ElementIdentifierInterface $elementIdentifier,
        ElementLocator $expectedElementLocator
    ) {
        $transpilationResult = $this->factory->createConstructorCall($elementIdentifier);
        $executableCall = $this->executableCallFactory->create($transpilationResult);

        $elementLocator = eval($executableCall);

        $this->assertEquals($expectedElementLocator, $elementLocator);
    }

    public function createConstructorCallDataProvider(): array
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

    public function testCreateConstructorCallThrowsNonTranspilableModelException()
    {
        $value = new ObjectValue(ValueTypes::PAGE_ELEMENT_REFERENCE, '', '', '');

        $elementIdentifier = \Mockery::mock(ElementIdentifierInterface::class);
        $elementIdentifier
            ->shouldReceive('getValue')
            ->andReturn($value);

        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "' . get_class($elementIdentifier) . '"');

        $this->factory->createConstructorCall($elementIdentifier);
    }
}
