<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\VariableNames;

class DomCrawlerNavigatorCallFactory
{
    private $elementCallArgumentFactory;

    public function __construct(ElementCallArgumentFactory $elementCallArgumentFactory)
    {
        $this->elementCallArgumentFactory = $elementCallArgumentFactory;
    }

    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory(
            ElementCallArgumentFactory::createFactory()
        );
    }

    public function createFindCall(DomIdentifierInterface $identifier): SourceInterface
    {
        return $this->createElementCall($identifier, 'find');
    }

    public function createFindOneCall(DomIdentifierInterface $identifier): SourceInterface
    {
        return $this->createElementCall($identifier, 'findOne');
    }

    public function createHasCall(DomIdentifierInterface $identifier): SourceInterface
    {
        return $this->createElementCall($identifier, 'has');
    }

    public function createHasOneCall(DomIdentifierInterface $identifier): SourceInterface
    {
        return $this->createElementCall($identifier, 'hasOne');
    }

    private function createElementCall(
        DomIdentifierInterface $identifier,
        string $methodName
    ): SourceInterface {
        $arguments = $this->elementCallArgumentFactory->createElementCallArguments($identifier);

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
