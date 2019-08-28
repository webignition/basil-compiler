<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilTranspiler\Model\TranspilationResult;

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
     * @param ElementIdentifierInterface $elementIdentifier
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    public function createFindElementCallForIdentifier(
        ElementIdentifierInterface $elementIdentifier
    ): TranspilationResult {
        $arguments = $this->createElementCallArguments($elementIdentifier);

        return $this->createFindElementCallForTranspiledArguments($arguments);
    }

    /**
     * @param TranspilationResult $arguments
     *
     * @return TranspilationResult
     */
    public function createFindElementCallForTranspiledArguments(TranspilationResult $arguments): TranspilationResult
    {
        return $this->createElementCall($arguments, 'findElement');
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     *
     * @return TranspilationResult
     *
     * @throws NonTranspilableModelException
     */
    public function createHasElementCallForIdentifier(
        ElementIdentifierInterface $elementIdentifier
    ): TranspilationResult {
        $hasElementCallArguments = $this->createElementCallArguments($elementIdentifier);

        return $this->createHasElementCallForTranspiledArguments($hasElementCallArguments);
    }

    /**
     * @param TranspilationResult $arguments
     *
     * @return TranspilationResult
     */
    public function createHasElementCallForTranspiledArguments(TranspilationResult $arguments): TranspilationResult
    {
        return $this->createElementCall($arguments, 'hasElement');
    }


    /**
     * @param TranspilationResult $arguments
     * @param string $methodName
     *
     * @return TranspilationResult
     */
    private function createElementCall(
        TranspilationResult $arguments,
        string $methodName
    ): TranspilationResult {
        $domCrawlerNavigatorPlaceholder = new VariablePlaceholder(VariableNames::DOM_CRAWLER_NAVIGATOR);

        $transpilationResult = new TranspilationResult(
            (string) $domCrawlerNavigatorPlaceholder . '->' . $methodName . '(' . (string) $arguments . ')'
        );

        return $transpilationResult->withAdditionalUseStatements($arguments->getUseStatements());
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

        $transpilationResult = new TranspilationResult($findElementArguments);

        return $transpilationResult->withAdditionalUseStatements($useStatements);
    }
}
