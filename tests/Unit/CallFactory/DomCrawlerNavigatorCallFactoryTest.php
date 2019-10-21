<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\CallFactory;

use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Identifier\DomIdentifier;
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
     * @var MetadataInterface
     */
    private $expectedMetadata;

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

        $this->expectedMetadata = (new Metadata())
            ->withClassDependencies($expectedClassDependencies)
            ->withVariableDependencies($expectedVariableDependencies);
    }

    public function testCreateFindCallForTranspiledLocator()
    {
        $identifier = new DomIdentifier('.selector');

        $source = $this->factory->createFindCall($identifier);

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->find\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $source);

        $this->assertEquals($this->expectedMetadata, $source->getMetadata());
    }

    public function testCreateHasCall()
    {
        $identifier = new DomIdentifier('.selector');

        $source = $this->factory->createHasCall($identifier);

        $expectedContentPattern = '/^' . $this->domCrawlerNavigatorVariablePlaceholder . '->has\(.*\)$/';
        $this->assertRegExp($expectedContentPattern, (string) $source);

        $this->assertEquals($this->expectedMetadata, $source->getMetadata());
    }
}
