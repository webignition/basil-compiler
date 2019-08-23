<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;

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
     * @return string
     *
     * @throws NonTranspilableModelException
     */
    public function createFindElementCall(
        ElementIdentifierInterface $elementIdentifier,
        array $variableIdentifiers
    ): string {
        $targetElementLocatorConstructorCall =
            $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();

        $findElementArguments = $targetElementLocatorConstructorCall;

        if ($parentIdentifier instanceof ElementIdentifierInterface) {
            $parentElementLocatorConstructorCall =
                $this->elementLocatorCallFactory->createConstructorCall($parentIdentifier);

            $findElementArguments .= ', ' . $parentElementLocatorConstructorCall;
        }

        $domCrawlerNavigator = $this->variableNameResolver->resolve(
            '{{ ' . VariableNames::DOM_CRAWLER_NAVIGATOR . ' }}',
            $variableIdentifiers
        );

        return $domCrawlerNavigator . '->findElement(' . $findElementArguments . ')';
    }
}
