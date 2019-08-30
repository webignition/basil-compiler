<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilTranspiler\CallFactory\ElementLocatorCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
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
     */
    public function createFindCallForIdentifier(
        ElementIdentifierInterface $elementIdentifier
    ): TranspilationResultInterface {
        $arguments = $this->createElementCallArguments($elementIdentifier);

        return $this->createFindCallForTranspiledArguments($arguments);
    }

    /**
     * @param TranspilationResultInterface $arguments
     *
     * @return TranspilationResultInterface
     */
    public function createFindCallForTranspiledArguments(
        TranspilationResultInterface $arguments
    ): TranspilationResultInterface {
        return $this->createElementCall($arguments, 'find');
    }

    /**
     * @param TranspilationResultInterface $arguments
     *
     * @return TranspilationResultInterface
     */
    public function createFindOneCallForTranspiledArguments(
        TranspilationResultInterface $arguments
    ): TranspilationResultInterface {
        return $this->createElementCall($arguments, 'findOne');
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createHasCallForIdentifier(
        ElementIdentifierInterface $elementIdentifier
    ): TranspilationResultInterface {
        $hasElementCallArguments = $this->createElementCallArguments($elementIdentifier);

        return $this->createHasCallForTranspiledArguments($hasElementCallArguments);
    }

    /**
     * @param TranspilationResultInterface $arguments
     *
     * @return TranspilationResultInterface
     */
    public function createHasCallForTranspiledArguments(
        TranspilationResultInterface $arguments
    ): TranspilationResultInterface {
        return $this->createElementCall($arguments, 'has');
    }

    /**
     * @param TranspilationResultInterface $arguments
     *
     * @return TranspilationResultInterface
     */
    public function createHasOneCallForTranspiledArguments(
        TranspilationResultInterface $arguments
    ): TranspilationResultInterface {
        return $this->createElementCall($arguments, 'hasOne');
    }

    /**
     * @param TranspilationResultInterface $arguments
     * @param string $methodName
     *
     * @return TranspilationResultInterface
     */
    private function createElementCall(
        TranspilationResultInterface $arguments,
        string $methodName
    ): TranspilationResultInterface {
        $variablePlaceholders = new VariablePlaceholderCollection();
        $domCrawlerNavigatorPlaceholder = $variablePlaceholders->create(VariableNames::DOM_CRAWLER_NAVIGATOR);

        $template = (string) $domCrawlerNavigatorPlaceholder . '->' . $methodName . '(%s)';

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
