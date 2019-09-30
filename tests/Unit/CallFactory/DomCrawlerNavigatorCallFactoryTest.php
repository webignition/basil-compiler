<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\CallFactory;

use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\VariableNames;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\DomElementLocator\ElementLocator;

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

    /**
     * @var UseStatementCollection
     */
    private $expectedUseStatements;

    /**
     * @var VariablePlaceholderCollection
     */
    private $expectedPlaceholders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();

        $this->expectedUseStatements = new UseStatementCollection([
            new UseStatement(ElementLocator::class),
        ]);

        $this->expectedPlaceholders = VariablePlaceholderCollection::createCollection([
            VariableNames::DOM_CRAWLER_NAVIGATOR
        ]);

        $this->domCrawlerNavigatorVariablePlaceholder =
            $this->expectedPlaceholders->get(VariableNames::DOM_CRAWLER_NAVIGATOR);
    }

    public function testCreateFindCallForIdentifier()
    {
        $transpilableSource = $this->factory->createFindCallForIdentifier(
            TestIdentifierFactory::createElementIdentifier('.selector')
        );

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->find\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $transpilableSource);

        $this->assertEquals($this->expectedUseStatements, $transpilableSource->getUseStatements());
        $this->assertEquals($this->expectedPlaceholders, $transpilableSource->getVariablePlaceholders());
    }

    public function testCreateFindCallForTranspiledLocator()
    {
        $identifier = TestIdentifierFactory::createElementIdentifier('.selector');

        $findElementCallArguments = $this->factory->createElementCallArguments($identifier);

        $transpilableSource = $this->factory->createFindCallForTranspiledArguments($findElementCallArguments);

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->find\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $transpilableSource);

        $this->assertEquals($this->expectedUseStatements, $transpilableSource->getUseStatements());
        $this->assertEquals($this->expectedPlaceholders, $transpilableSource->getVariablePlaceholders());
    }

    public function testCreateHasCallForIdentifier()
    {
        $transpilableSource = $this->factory->createHasCallForIdentifier(
            TestIdentifierFactory::createElementIdentifier('.selector')
        );

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->has\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $transpilableSource);

        $this->assertEquals($this->expectedUseStatements, $transpilableSource->getUseStatements());
        $this->assertEquals($this->expectedPlaceholders, $transpilableSource->getVariablePlaceholders());
    }

    public function testCreateHasCallForTranspiledLocator()
    {
        $identifier = TestIdentifierFactory::createElementIdentifier('.selector');

        $hasElementCallArguments = $this->factory->createElementCallArguments($identifier);

        $transpilableSource = $this->factory->createHasCallForTranspiledArguments($hasElementCallArguments);

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->has\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $transpilableSource);

        $this->assertEquals($this->expectedUseStatements, $transpilableSource->getUseStatements());
        $this->assertEquals($this->expectedPlaceholders, $transpilableSource->getVariablePlaceholders());
    }
}
