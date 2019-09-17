<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Identifier;

use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class DomIdentifierTranspiler implements TranspilerInterface
{
    private $domCrawlerNavigatorCallFactory;
    private $variableAssignmentCallFactory;

    public function __construct(
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        VariableAssignmentCallFactory $variableAssignmentCallFactory
    ) {
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->variableAssignmentCallFactory = $variableAssignmentCallFactory;
    }

    public static function createTranspiler(): DomIdentifierTranspiler
    {
        return new DomIdentifierTranspiler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            VariableAssignmentCallFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof DomIdentifierInterface;
    }

    /**
     * @param object $model
     *
     * @return TranspilationResultInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): TranspilationResultInterface
    {
        if (!$model instanceof DomIdentifierInterface) {
            throw new NonTranspilableModelException($model);
        }

        $attributeName = $model->getAttributeName();

        if (null === $attributeName) {
            return $this->variableAssignmentCallFactory->createForElementCollection(
                $model,
                VariableAssignmentCallFactory::createElementLocatorPlaceholder(),
                VariableAssignmentCallFactory::createCollectionPlaceholder()
            );
        }

        if ('' === trim($attributeName)) {
            throw new NonTranspilableModelException($model);
        }

        return $this->variableAssignmentCallFactory->createForAttribute(
            $model,
            VariableAssignmentCallFactory::createElementLocatorPlaceholder(),
            VariableAssignmentCallFactory::createElementPlaceholder(),
            VariableAssignmentCallFactory::createAttributePlaceholder()
        );
    }
}
