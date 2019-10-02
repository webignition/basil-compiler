<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
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
        $variablePlaceholders = new VariablePlaceholderCollection();
        $domCrawlerNavigatorPlaceholder = $variablePlaceholders->create(VariableNames::DOM_CRAWLER_NAVIGATOR);

        $template = (string) $domCrawlerNavigatorPlaceholder . '->' . $methodName . '(%s)';

        return $arguments->extend($template, new UseStatementCollection(), $variablePlaceholders);
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     *
     * @return CompilableSourceInterface
     */
    public function createElementCallArguments(
        DomIdentifierInterface $elementIdentifier
    ): CompilableSourceInterface {
        $compilableSource = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();
        if ($parentIdentifier instanceof DomIdentifierInterface) {
            $parentCompilableSource = $this->elementLocatorCallFactory->createConstructorCall($parentIdentifier);

            $compilableSource = $compilableSource->extend(
                sprintf('%s, %s', '%s', (string) $parentCompilableSource),
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            );
        }

        return $compilableSource;
    }
}
