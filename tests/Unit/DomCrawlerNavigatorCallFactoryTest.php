<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit;

use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\DomCrawlerNavigatorCallFactory;
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

    public function testCreateHasElementCall()
    {
        $transpilationResult = $this->factory->createHasElementCall(
            TestIdentifierFactory::createCssElementIdentifier('.selector'),
            [
                VariableNames::DOM_CRAWLER_NAVIGATOR => '$domCrawlerNavigator',
            ]
        );

        $expectedContentPattern = '/^\$domCrawlerNavigator->hasElement\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, $transpilationResult->getContent());
    }
}
