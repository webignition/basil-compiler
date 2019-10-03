<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
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

    /**
     * @param DomIdentifierInterface $elementIdentifier
     *
     * @return CompilableSourceInterface
     */
    public function createFindCallForIdentifier(
        DomIdentifierInterface $elementIdentifier
    ): CompilableSourceInterface {
        $arguments = $this->createElementCallArguments($elementIdentifier);

        return $this->createFindCallForTranspiledArguments($arguments);
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
        $hasElementCallArguments = $this->createElementCallArguments($elementIdentifier);

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
        $classDependencies = new ClassDependencyCollection();
        $classDependencies = $classDependencies->merge([$arguments->getClassDependencies()]);

        $variableDependencies = new VariablePlaceholderCollection();
        $variableDependencies = $variableDependencies->merge([$arguments->getVariableDependencies()]);
        $domCrawlerNavigatorPlaceholder = $variableDependencies->create(VariableNames::DOM_CRAWLER_NAVIGATOR);

        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->merge([$arguments->getVariableExports()]);

        $createStatement = sprintf(
            (string) $domCrawlerNavigatorPlaceholder . '->' . $methodName . '(%s)',
            (string) $arguments
        );

        $compilableSource = new CompilableSource([$createStatement]);
        $compilableSource = $compilableSource->withClassDependencies($classDependencies);
        $compilableSource = $compilableSource->withVariableDependencies($variableDependencies);
        $compilableSource = $compilableSource->withVariableExports($variableExports);

        return $compilableSource;
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     *
     * @return CompilableSourceInterface
     */
    private function createElementCallArguments(
        DomIdentifierInterface $elementIdentifier
    ): CompilableSourceInterface {
        $compilableSource = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();
        if ($parentIdentifier instanceof DomIdentifierInterface) {
            $parentElementLocatorConstructorCall = $this->elementLocatorCallFactory->createConstructorCall(
                $parentIdentifier
            );

            $classDependencies = new ClassDependencyCollection();
            $classDependencies = $classDependencies->merge([
                $compilableSource->getClassDependencies(),
                $parentElementLocatorConstructorCall->getClassDependencies(),
            ]);

            $variableDependencies = new VariablePlaceholderCollection();
            $variableDependencies = $variableDependencies->merge([
                $compilableSource->getVariableDependencies(),
                $parentElementLocatorConstructorCall->getVariableDependencies(),
            ]);

            $variableExports = new VariablePlaceholderCollection();
            $variableExports = $variableExports->merge([
                $compilableSource->getVariableExports(),
                $compilableSource->getVariableDependencies(),
            ]);

            $compilableSource = new CompilableSource([
                sprintf(
                    '%s, %s',
                    (string) $compilableSource,
                    (string) $parentElementLocatorConstructorCall
                ),
            ]);

            $compilableSource = $compilableSource->withClassDependencies($classDependencies);
            $compilableSource = $compilableSource->withVariableDependencies($variableDependencies);
            $compilableSource = $compilableSource->withVariableExports($variableExports);
        }

        return $compilableSource;
    }
}
