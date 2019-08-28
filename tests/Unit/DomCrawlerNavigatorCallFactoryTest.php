<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit;

use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\VariableNames;
use webignition\BasilTranspiler\VariablePlaceholder;

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

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
        $this->domCrawlerNavigatorVariablePlaceholder = new VariablePlaceholder(VariableNames::DOM_CRAWLER_NAVIGATOR);
    }

    public function testCreateFindElementCallForIdentifier()
    {
        $transpilationResult = $this->factory->createFindElementCallForIdentifier(
            TestIdentifierFactory::createCssElementIdentifier('.selector')
        );

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->findElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());
    }

    public function testCreateFindElementCallForTranspiledLocator()
    {
        $identifier = TestIdentifierFactory::createCssElementIdentifier('.selector');

        $findElementCallArguments = $this->factory->createElementCallArguments($identifier);

        $transpilationResult = $this->factory->createFindElementCallForTranspiledArguments($findElementCallArguments);

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->findElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());
    }

    public function testCreateHasElementCallForIdentifier()
    {
        $transpilationResult = $this->factory->createHasElementCallForIdentifier(
            TestIdentifierFactory::createCssElementIdentifier('.selector')
        );

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->hasElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());
    }

    public function testCreateHasElementCallForTranspiledLocator()
    {
        $identifier = TestIdentifierFactory::createCssElementIdentifier('.selector');

        $hasElementCallArguments = $this->factory->createElementCallArguments($identifier);

        $transpilationResult = $this->factory->createHasElementCallForTranspiledArguments($hasElementCallArguments);

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->hasElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());
    }
}
