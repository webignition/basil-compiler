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
    private $elementLocatorCallFactory;

    public function __construct(ElementLocatorCallFactory $elementLocatorCallFactory)
    {
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
    }

    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory(
            ElementLocatorCallFactory::createFactory()
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
        $arguments = $this->createElementCallArguments($identifier);

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

    private function createElementCallArguments(DomIdentifierInterface $elementIdentifier): SourceInterface
    {
        $source = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();
        if ($parentIdentifier instanceof DomIdentifierInterface) {
            $parentElementLocatorConstructorCall = $this->elementLocatorCallFactory->createConstructorCall(
                $parentIdentifier
            );

            $metadata = (new Metadata())->merge([
                $source->getMetadata(),
                $parentElementLocatorConstructorCall->getMetadata(),
            ]);

            $source = (new Source())
                ->withStatements([
                    sprintf(
                        '%s, %s',
                        (string) $source,
                        (string) $parentElementLocatorConstructorCall
                    ),
                ])
                ->withMetadata($metadata);
        }

        return $source;
    }
}
