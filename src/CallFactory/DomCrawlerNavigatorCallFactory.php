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

    /**
     * @param SourceInterface $arguments
     *
     * @return SourceInterface
     */
    public function createFindCallForTranspiledArguments(
        SourceInterface $arguments
    ): SourceInterface {
        return $this->createElementCall($arguments, 'find');
    }

    /**
     * @param SourceInterface $arguments
     *
     * @return SourceInterface
     */
    public function createFindOneCallForTranspiledArguments(
        SourceInterface $arguments
    ): SourceInterface {
        return $this->createElementCall($arguments, 'findOne');
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     *
     * @return SourceInterface
     */
    public function createHasCallForIdentifier(
        DomIdentifierInterface $elementIdentifier
    ): SourceInterface {
        $hasElementCallArguments = $this->elementCallArgumentFactory->createElementCallArguments($elementIdentifier);

        return $this->createHasCallForTranspiledArguments($hasElementCallArguments);
    }

    /**
     * @param SourceInterface $arguments
     *
     * @return SourceInterface
     */
    public function createHasCallForTranspiledArguments(
        SourceInterface $arguments
    ): SourceInterface {
        return $this->createElementCall($arguments, 'has');
    }

    /**
     * @param SourceInterface $arguments
     *
     * @return SourceInterface
     */
    public function createHasOneCallForTranspiledArguments(
        SourceInterface $arguments
    ): SourceInterface {
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
