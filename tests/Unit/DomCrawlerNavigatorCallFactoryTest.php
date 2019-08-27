<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit;

use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\VariableNames;

class DomCrawlerNavigatorCallFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DomCrawlerNavigatorCallFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
    }

    public function testCreateFindElementCallForIdentifier()
    {
        $transpilationResult = $this->factory->createFindElementCallForIdentifier(
            TestIdentifierFactory::createCssElementIdentifier('.selector'),
            [
                VariableNames::DOM_CRAWLER_NAVIGATOR => '$domCrawlerNavigator',
            ]
        );

        $expectedContentPattern = '/^\$domCrawlerNavigator->findElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());
    }

    public function testCreateFindElementCallForTranspiledLocator()
    {
        $identifier = TestIdentifierFactory::createCssElementIdentifier('.selector');

        $findElementCallArguments = $this->factory->createElementCallArguments($identifier);

        $transpilationResult = $this->factory->createFindElementCallForTranspiledArguments(
            $findElementCallArguments,
            [
                VariableNames::DOM_CRAWLER_NAVIGATOR => '$domCrawlerNavigator',
            ]
        );

        $expectedContentPattern = '/^\$domCrawlerNavigator->findElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());
    }

    public function testCreateHasElementCallForIdentifier()
    {
        $transpilationResult = $this->factory->createHasElementCallForIdentifier(
            TestIdentifierFactory::createCssElementIdentifier('.selector'),
            [
                VariableNames::DOM_CRAWLER_NAVIGATOR => '$domCrawlerNavigator',
            ]
        );

        $expectedContentPattern = '/^\$domCrawlerNavigator->hasElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());
    }

    public function testCreateHasElementCallForTranspiledLocator()
    {
        $identifier = TestIdentifierFactory::createCssElementIdentifier('.selector');

        $hasElementCallArguments = $this->factory->createElementCallArguments($identifier);

        $transpilationResult = $this->factory->createHasElementCallForTranspiledArguments(
            $hasElementCallArguments,
            [
                VariableNames::DOM_CRAWLER_NAVIGATOR => '$domCrawlerNavigator',
            ]
        );

        $expectedContentPattern = '/^\$domCrawlerNavigator->hasElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());
    }
}
