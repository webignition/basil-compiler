<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;

class DomCrawlerNavigatorCallFactory
{
    private $elementLocatorFactory;
    private $variableNameResolver;

    public function __construct(
        ElementLocatorFactory $elementLocatorFactory,
        VariableNameResolver $variableNameResolver
    ) {
        $this->elementLocatorFactory = $elementLocatorFactory;
        $this->variableNameResolver = $variableNameResolver;
    }

    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory(
            ElementLocatorFactory::createFactory(),
            new VariableNameResolver()
        );
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws NonTranspilableModelException
     */
    public function createFindElementCall(
        ElementIdentifierInterface $elementIdentifier,
        array $variableIdentifiers
    ): string {
        $targetElementLocatorConstructorCall =
            $this->elementLocatorFactory->createElementLocatorConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();

        $findElementArguments = $targetElementLocatorConstructorCall;

        if ($parentIdentifier instanceof ElementIdentifierInterface) {
            $parentElementLocatorConstructorCall =
                $this->elementLocatorFactory->createElementLocatorConstructorCall($parentIdentifier);

            $findElementArguments .= ', ' . $parentElementLocatorConstructorCall;
        }

        $domCrawlerNavigator = $this->variableNameResolver->resolve(
            '{{ ' . VariableNames::DOM_CRAWLER_NAVIGATOR . ' }}',
            $variableIdentifiers
        );

        return $domCrawlerNavigator . '->findElement(' . $findElementArguments . ')';
    }
}
