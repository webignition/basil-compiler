<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\Model\TranspilableSourceInterface;
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
     * @return TranspilableSourceInterface
     */
    public function createFindCallForIdentifier(
        DomIdentifierInterface $elementIdentifier
    ): TranspilableSourceInterface {
        $arguments = $this->createElementCallArguments($elementIdentifier);

        return $this->createFindCallForTranspiledArguments($arguments);
    }

    /**
     * @param TranspilableSourceInterface $arguments
     *
     * @return TranspilableSourceInterface
     */
    public function createFindCallForTranspiledArguments(
        TranspilableSourceInterface $arguments
    ): TranspilableSourceInterface {
        return $this->createElementCall($arguments, 'find');
    }

    /**
     * @param TranspilableSourceInterface $arguments
     *
     * @return TranspilableSourceInterface
     */
    public function createFindOneCallForTranspiledArguments(
        TranspilableSourceInterface $arguments
    ): TranspilableSourceInterface {
        return $this->createElementCall($arguments, 'findOne');
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     *
     * @return TranspilableSourceInterface
     */
    public function createHasCallForIdentifier(
        DomIdentifierInterface $elementIdentifier
    ): TranspilableSourceInterface {
        $hasElementCallArguments = $this->createElementCallArguments($elementIdentifier);

        return $this->createHasCallForTranspiledArguments($hasElementCallArguments);
    }

    /**
     * @param TranspilableSourceInterface $arguments
     *
     * @return TranspilableSourceInterface
     */
    public function createHasCallForTranspiledArguments(
        TranspilableSourceInterface $arguments
    ): TranspilableSourceInterface {
        return $this->createElementCall($arguments, 'has');
    }

    /**
     * @param TranspilableSourceInterface $arguments
     *
     * @return TranspilableSourceInterface
     */
    public function createHasOneCallForTranspiledArguments(
        TranspilableSourceInterface $arguments
    ): TranspilableSourceInterface {
        return $this->createElementCall($arguments, 'hasOne');
    }

    /**
     * @param TranspilableSourceInterface $arguments
     * @param string $methodName
     *
     * @return TranspilableSourceInterface
     */
    private function createElementCall(
        TranspilableSourceInterface $arguments,
        string $methodName
    ): TranspilableSourceInterface {
        $variablePlaceholders = new VariablePlaceholderCollection();
        $domCrawlerNavigatorPlaceholder = $variablePlaceholders->create(VariableNames::DOM_CRAWLER_NAVIGATOR);

        $template = (string) $domCrawlerNavigatorPlaceholder . '->' . $methodName . '(%s)';

        return $arguments->extend($template, new UseStatementCollection(), $variablePlaceholders);
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     *
     * @return TranspilableSourceInterface
     */
    public function createElementCallArguments(
        DomIdentifierInterface $elementIdentifier
    ): TranspilableSourceInterface {
        $transpilableSource = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();
        if ($parentIdentifier instanceof DomIdentifierInterface) {
            $parentTranspilableSource = $this->elementLocatorCallFactory->createConstructorCall($parentIdentifier);

            $transpilableSource = $transpilableSource->extend(
                sprintf('%s, %s', '%s', (string) $parentTranspilableSource),
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            );
        }

        return $transpilableSource;
    }
}
