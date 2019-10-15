<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\CallFactory;

use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilCompilationSource\CompilationMetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\VariableNames;
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
     * @var CompilationMetadataInterface
     */
    private $expectedCompilationMetadata;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();

        $expectedClassDependencies = new ClassDependencyCollection([
            new ClassDependency(ElementLocator::class),
        ]);

        $expectedVariableDependencies = new VariablePlaceholderCollection();
        $this->domCrawlerNavigatorVariablePlaceholder = $expectedVariableDependencies->create(
            VariableNames::DOM_CRAWLER_NAVIGATOR
        );

        $this->expectedCompilationMetadata = (new CompilationMetadata())
            ->withClassDependencies($expectedClassDependencies)
            ->withVariableDependencies($expectedVariableDependencies);
    }

    public function testCreateFindCallForIdentifier()
    {
        $compilableSource = $this->factory->createFindCallForIdentifier(
            TestIdentifierFactory::createElementIdentifier('.selector')
        );

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->find\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $compilableSource);

        $this->assertEquals($this->expectedCompilationMetadata, $compilableSource->getCompilationMetadata());
    }

    public function testCreateFindCallForTranspiledLocator()
    {
        $findElementCallArguments = (new CompilableSource())
            ->withStatements([
                'new ElementLocator(\'.selector\')'
            ])
            ->withCompilationMetadata(
                (new CompilationMetadata())->withClassDependencies(new ClassDependencyCollection([
                    new ClassDependency(ElementLocator::class)
                ]))
            );

        $compilableSource = $this->factory->createFindCallForTranspiledArguments($findElementCallArguments);

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->find\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $compilableSource);

        $this->assertEquals($this->expectedCompilationMetadata, $compilableSource->getCompilationMetadata());
    }

    public function testCreateHasCallForIdentifier()
    {
        $compilableSource = $this->factory->createHasCallForIdentifier(
            TestIdentifierFactory::createElementIdentifier('.selector')
        );

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->has\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $compilableSource);

        $this->assertEquals($this->expectedCompilationMetadata, $compilableSource->getCompilationMetadata());
    }

    public function testCreateHasCallForTranspiledLocator()
    {
        $hasElementCallArguments = (new CompilableSource())
            ->withStatements([
                'new ElementLocator(\'.selector\')'
            ])
            ->withCompilationMetadata(
                (new CompilationMetadata())->withClassDependencies(new ClassDependencyCollection([
                    new ClassDependency(ElementLocator::class)
                ]))
            );

        $compilableSource = $this->factory->createHasCallForTranspiledArguments($hasElementCallArguments);

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->has\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $compilableSource);

        $this->assertEquals($this->expectedCompilationMetadata, $compilableSource->getCompilationMetadata());
    }
}
