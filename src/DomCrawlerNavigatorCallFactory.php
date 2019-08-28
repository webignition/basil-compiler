<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;

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
        $template = (string) $domCrawlerNavigatorPlaceholder . '->' . $methodName . '(%s)';

        return $arguments->extend($template, new UseStatementCollection(), new VariablePlaceholderCollection());
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
        $transpilationResult = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();
        if ($parentIdentifier instanceof ElementIdentifierInterface) {
            $parentTranspilationResult = $this->elementLocatorCallFactory->createConstructorCall($parentIdentifier);

            $transpilationResult = $transpilationResult->extend(
                sprintf('%s, %s', '%s', $parentTranspilationResult->getContent()),
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            );
        }

        return $transpilationResult;
    }
}
