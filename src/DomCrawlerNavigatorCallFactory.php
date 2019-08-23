<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilTranspiler\Model\TranspilationResult;

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
    public function createFindElementCall(
        ElementIdentifierInterface $elementIdentifier,
        array $variableIdentifiers
    ): TranspilationResult {
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

        $domCrawlerNavigator = $this->variableNameResolver->resolve(
            '{{ ' . VariableNames::DOM_CRAWLER_NAVIGATOR . ' }}',
            $variableIdentifiers
        );

        return new TranspilationResult(
            $domCrawlerNavigator . '->findElement(' . $findElementArguments . ')',
            $useStatements
        );
    }
}
