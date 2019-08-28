<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit;

use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\VariableNames;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;
use webignition\SymfonyDomCrawlerNavigator\Model\LocatorType;

class DomCrawlerNavigatorCallFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DomCrawlerNavigatorCallFactory
     */
    private $factory;

    /**
     * @var VariablePlaceholder
     */
    private $domCrawlerNavigatorVariablePlaceholder;

    private $expectedUseStatements = [];
    private $expectedPlaceholders = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
        $this->domCrawlerNavigatorVariablePlaceholder = new VariablePlaceholder(VariableNames::DOM_CRAWLER_NAVIGATOR);

        $this->expectedUseStatements = [
            new UseStatement(ElementLocator::class),
            new UseStatement(LocatorType::class),
        ];

        $this->expectedPlaceholders = [
            $this->domCrawlerNavigatorVariablePlaceholder,
        ];
    }

    public function testCreateFindElementCallForIdentifier()
    {
        $transpilationResult = $this->factory->createFindElementCallForIdentifier(
            TestIdentifierFactory::createCssElementIdentifier('.selector')
        );

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->findElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());

        $this->assertEquals($this->expectedUseStatements, $transpilationResult->getUseStatements()->getAll());
        $this->assertEquals($this->expectedPlaceholders, $transpilationResult->getVariablePlaceholders()->getAll());
    }

    public function testCreateFindElementCallForTranspiledLocator()
    {
        $identifier = TestIdentifierFactory::createCssElementIdentifier('.selector');

        $findElementCallArguments = $this->factory->createElementCallArguments($identifier);

        $transpilationResult = $this->factory->createFindElementCallForTranspiledArguments($findElementCallArguments);

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->findElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());

        $this->assertEquals($this->expectedUseStatements, $transpilationResult->getUseStatements()->getAll());
        $this->assertEquals($this->expectedPlaceholders, $transpilationResult->getVariablePlaceholders()->getAll());
    }

    public function testCreateHasElementCallForIdentifier()
    {
        $transpilationResult = $this->factory->createHasElementCallForIdentifier(
            TestIdentifierFactory::createCssElementIdentifier('.selector')
        );

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->hasElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());

        $this->assertEquals($this->expectedUseStatements, $transpilationResult->getUseStatements()->getAll());
        $this->assertEquals($this->expectedPlaceholders, $transpilationResult->getVariablePlaceholders()->getAll());
    }

    public function testCreateHasElementCallForTranspiledLocator()
    {
        $identifier = TestIdentifierFactory::createCssElementIdentifier('.selector');

        $hasElementCallArguments = $this->factory->createElementCallArguments($identifier);

        $transpilationResult = $this->factory->createHasElementCallForTranspiledArguments($hasElementCallArguments);

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->hasElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());

        $this->assertEquals($this->expectedUseStatements, $transpilationResult->getUseStatements()->getAll());
        $this->assertEquals($this->expectedPlaceholders, $transpilationResult->getVariablePlaceholders()->getAll());
    }
}
