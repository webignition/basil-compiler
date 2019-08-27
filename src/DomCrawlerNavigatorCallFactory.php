<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;

class DomCrawlerNavigatorCallFactory
{
    private $elementLocatorCallFactory;
    private $variableNameResolver;

    public function __construct(
        ElementLocatorCallFactory $elementLocatorCallFactory,
        VariableNameResolver $variableNameResolver
    ) {
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->variableNameResolver = $variableNameResolver;
    }

    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory(
            ElementLocatorCallFactory::createFactory(),
            new VariableNameResolver()
        );
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     * @param array $variableIdentifiers
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    public function createFindElementCallForIdentifier(
        ElementIdentifierInterface $elementIdentifier,
        array $variableIdentifiers
    ): TranspilationResult {
        $arguments = $this->createElementCallArguments($elementIdentifier);

        return $this->createFindElementCallForTranspiledArguments($arguments, $variableIdentifiers);
    }

    /**
     * @param TranspilationResult $arguments
     * @param array $variableIdentifiers
     *
     * @return TranspilationResult
     */
    public function createFindElementCallForTranspiledArguments(
        TranspilationResult $arguments,
        array $variableIdentifiers
    ): TranspilationResult {
        return $this->createElementCall($arguments, 'findElement', $variableIdentifiers);
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     * @param array $variableIdentifiers
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    public function createHasElementCallForIdentifier(
        ElementIdentifierInterface $elementIdentifier,
        array $variableIdentifiers
    ): TranspilationResult {
        $hasElementCallArguments = $this->createElementCallArguments($elementIdentifier);

        return $this->createElementCall($hasElementCallArguments, 'hasElement', $variableIdentifiers);
    }

    /**
     * @param TranspilationResult $arguments
     * @param string $methodName
     * @param array $variableIdentifiers
     *
     * @return TranspilationResult
     */
    private function createElementCall(
        TranspilationResult $arguments,
        string $methodName,
        array $variableIdentifiers
    ): TranspilationResult {
        $domCrawlerNavigator = $this->variableNameResolver->resolve(
            (string) new VariablePlaceholder(VariableNames::DOM_CRAWLER_NAVIGATOR),
            $variableIdentifiers
        );

        return new TranspilationResult(
            $domCrawlerNavigator . '->' . $methodName . '(' . (string) $arguments . ')',
            $arguments->getUseStatements()
        );
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    public function createElementCallArguments(ElementIdentifierInterface $elementIdentifier): TranspilationResult
    {
        $elementTranspilationResult = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);
        $useStatements = $elementTranspilationResult->getUseStatements();

        $parentIdentifier = $elementIdentifier->getParentIdentifier();

        $findElementArguments = (string) $elementTranspilationResult;

        if ($parentIdentifier instanceof ElementIdentifierInterface) {
            $parentTranspilationResult = $this->elementLocatorCallFactory->createConstructorCall($parentIdentifier);
            $useStatements = $useStatements->withAdditionalUseStatements(
                $parentTranspilationResult->getUseStatements()
            );

            $findElementArguments .= ', ' . (string) $parentTranspilationResult;
        }

        return new TranspilationResult($findElementArguments, $useStatements);
    }
}
