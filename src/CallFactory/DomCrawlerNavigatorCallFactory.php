<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilTranspiler\VariableNames;

class DomCrawlerNavigatorCallFactory
{
    private $elementLocatorCallFactory;
    private $elementCallArgumentFactory;

    public function __construct(
        ElementLocatorCallFactory $elementLocatorCallFactory,
        ElementCallArgumentFactory $elementCallArgumentFactory
    ) {
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->elementCallArgumentFactory = $elementCallArgumentFactory;
    }

    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory(
            ElementLocatorCallFactory::createFactory(),
            ElementCallArgumentFactory::createFactory()
        );
    }

    public function createFindCall(SourceInterface $arguments): SourceInterface
    {
        return $this->createElementCall($arguments, 'find');
    }

    public function createFindOneCall(SourceInterface $arguments): SourceInterface
    {
        return $this->createElementCall($arguments, 'findOne');
    }

    public function createHasCall(SourceInterface $arguments): SourceInterface
    {
        return $this->createElementCall($arguments, 'has');
    }

    public function createHasOneCall(SourceInterface $arguments): SourceInterface
    {
        return $this->createElementCall($arguments, 'hasOne');
    }

    /**
     * @param SourceInterface $arguments
     * @param string $methodName
     *
     * @return SourceInterface
     */
    private function createElementCall(
        SourceInterface $arguments,
        string $methodName
    ): SourceInterface {
        $variableDependencies = new VariablePlaceholderCollection();
        $domCrawlerNavigatorPlaceholder = $variableDependencies->create(VariableNames::DOM_CRAWLER_NAVIGATOR);

        $metadata = (new Metadata())
            ->merge([$arguments->getMetadata()])
            ->withAdditionalVariableDependencies($variableDependencies);

        $createStatement = sprintf(
            (string) $domCrawlerNavigatorPlaceholder . '->' . $methodName . '(%s)',
            (string) $arguments
        );

        return (new Source())
            ->withStatements([$createStatement])
            ->withMetadata($metadata);
    }
}
