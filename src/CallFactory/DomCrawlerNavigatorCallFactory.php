<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadata;
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
     * @param CompilableSourceInterface $arguments
     *
     * @return CompilableSourceInterface
     */
    public function createFindCallForTranspiledArguments(
        CompilableSourceInterface $arguments
    ): CompilableSourceInterface {
        return $this->createElementCall($arguments, 'find');
    }

    /**
     * @param CompilableSourceInterface $arguments
     *
     * @return CompilableSourceInterface
     */
    public function createFindOneCallForTranspiledArguments(
        CompilableSourceInterface $arguments
    ): CompilableSourceInterface {
        return $this->createElementCall($arguments, 'findOne');
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     *
     * @return CompilableSourceInterface
     */
    public function createHasCallForIdentifier(
        DomIdentifierInterface $elementIdentifier
    ): CompilableSourceInterface {
        $hasElementCallArguments = $this->elementCallArgumentFactory->createElementCallArguments($elementIdentifier);

        return $this->createHasCallForTranspiledArguments($hasElementCallArguments);
    }

    /**
     * @param CompilableSourceInterface $arguments
     *
     * @return CompilableSourceInterface
     */
    public function createHasCallForTranspiledArguments(
        CompilableSourceInterface $arguments
    ): CompilableSourceInterface {
        return $this->createElementCall($arguments, 'has');
    }

    /**
     * @param CompilableSourceInterface $arguments
     *
     * @return CompilableSourceInterface
     */
    public function createHasOneCallForTranspiledArguments(
        CompilableSourceInterface $arguments
    ): CompilableSourceInterface {
        return $this->createElementCall($arguments, 'hasOne');
    }

    /**
     * @param CompilableSourceInterface $arguments
     * @param string $methodName
     *
     * @return CompilableSourceInterface
     */
    private function createElementCall(
        CompilableSourceInterface $arguments,
        string $methodName
    ): CompilableSourceInterface {
        $variableDependencies = new VariablePlaceholderCollection();
        $domCrawlerNavigatorPlaceholder = $variableDependencies->create(VariableNames::DOM_CRAWLER_NAVIGATOR);

        $compilationMetadata = (new CompilationMetadata())
            ->merge([$arguments->getCompilationMetadata()])
            ->withAdditionalVariableDependencies($variableDependencies);

        $createStatement = sprintf(
            (string) $domCrawlerNavigatorPlaceholder . '->' . $methodName . '(%s)',
            (string) $arguments
        );

        return (new CompilableSource())
            ->withStatements([$createStatement])
            ->withCompilationMetadata($compilationMetadata);
    }
}
