<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\UnknownItemException;
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
     * @param ElementIdentifierInterface $elementIdentifier
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     * @throws UnknownItemException
     */
    public function createFindElementCallForIdentifier(
        ElementIdentifierInterface $elementIdentifier
    ): TranspilationResultInterface {
        $arguments = $this->createElementCallArguments($elementIdentifier);

        return $this->createFindElementCallForTranspiledArguments($arguments);
    }

    /**
     * @param TranspilationResultInterface $arguments
     *
     * @return TranspilationResultInterface
     * @throws UnknownItemException
     */
    public function createFindElementCallForTranspiledArguments(
        TranspilationResultInterface $arguments
    ): TranspilationResultInterface {
        return $this->createElementCall($arguments, 'findElement');
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     * @throws UnknownItemException
     */
    public function createHasElementCallForIdentifier(
        ElementIdentifierInterface $elementIdentifier
    ): TranspilationResultInterface {
        $hasElementCallArguments = $this->createElementCallArguments($elementIdentifier);

        return $this->createHasElementCallForTranspiledArguments($hasElementCallArguments);
    }

    /**
     * @param TranspilationResultInterface $arguments
     *
     * @return TranspilationResultInterface
     * @throws UnknownItemException
     */
    public function createHasElementCallForTranspiledArguments(
        TranspilationResultInterface $arguments
    ): TranspilationResultInterface {
        return $this->createElementCall($arguments, 'hasElement');
    }


    /**
     * @param TranspilationResultInterface $arguments
     * @param string $methodName
     *
     * @return TranspilationResultInterface
     * @throws UnknownItemException
     */
    private function createElementCall(
        TranspilationResultInterface $arguments,
        string $methodName
    ): TranspilationResultInterface {
        $variablePlaceholders = VariablePlaceholderCollection::createCollection([
            VariableNames::DOM_CRAWLER_NAVIGATOR,
        ]);

        $template =
            (string) $variablePlaceholders->get(VariableNames::DOM_CRAWLER_NAVIGATOR) . '->' . $methodName . '(%s)';

        return $arguments->extend($template, new UseStatementCollection(), $variablePlaceholders);
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createElementCallArguments(
        ElementIdentifierInterface $elementIdentifier
    ): TranspilationResultInterface {
        $transpilationResult = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();
        if ($parentIdentifier instanceof ElementIdentifierInterface) {
            $parentTranspilationResult = $this->elementLocatorCallFactory->createConstructorCall($parentIdentifier);

            $transpilationResult = $transpilationResult->extend(
                sprintf('%s, %s', '%s', (string) $parentTranspilationResult),
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            );
        }

        return $transpilationResult;
    }
}
