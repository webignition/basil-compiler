<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\CallFactory;

use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
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
            new UseStatement(LocatorType::class),
        ]);

        $this->expectedPlaceholders = VariablePlaceholderCollection::createCollection([
            VariableNames::DOM_CRAWLER_NAVIGATOR
        ]);

        $this->domCrawlerNavigatorVariablePlaceholder =
            $this->expectedPlaceholders->get(VariableNames::DOM_CRAWLER_NAVIGATOR);
    }

    public function testCreateFindCallForIdentifier()
    {
        $transpilationResult = $this->factory->createFindCallForIdentifier(
            TestIdentifierFactory::createElementIdentifier(
                new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
            )
        );

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->find\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $transpilationResult);

        $this->assertEquals($this->expectedUseStatements, $transpilationResult->getUseStatements());
        $this->assertEquals($this->expectedPlaceholders, $transpilationResult->getVariablePlaceholders());
    }

    public function testCreateFindCallForTranspiledLocator()
    {
        $identifier = TestIdentifierFactory::createElementIdentifier(
            new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
        );

        $findElementCallArguments = $this->factory->createElementCallArguments($identifier);

        $transpilationResult = $this->factory->createFindCallForTranspiledArguments($findElementCallArguments);

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->find\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $transpilationResult);

        $this->assertEquals($this->expectedUseStatements, $transpilationResult->getUseStatements());
        $this->assertEquals($this->expectedPlaceholders, $transpilationResult->getVariablePlaceholders());
    }

    public function testCreateHasCallForIdentifier()
    {
        $transpilationResult = $this->factory->createHasCallForIdentifier(
            TestIdentifierFactory::createElementIdentifier(
                new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
            )
        );

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->has\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $transpilationResult);

        $this->assertEquals($this->expectedUseStatements, $transpilationResult->getUseStatements());
        $this->assertEquals($this->expectedPlaceholders, $transpilationResult->getVariablePlaceholders());
    }

    public function testCreateHasCallForTranspiledLocator()
    {
        $identifier = TestIdentifierFactory::createElementIdentifier(
            new ElementExpression('.selector', ElementExpressionType::CSS_SELECTOR)
        );

        $hasElementCallArguments = $this->factory->createElementCallArguments($identifier);

        $transpilationResult = $this->factory->createHasCallForTranspiledArguments($hasElementCallArguments);

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->has\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $transpilationResult);

        $this->assertEquals($this->expectedUseStatements, $transpilationResult->getUseStatements());
        $this->assertEquals($this->expectedPlaceholders, $transpilationResult->getVariablePlaceholders());
    }
}
