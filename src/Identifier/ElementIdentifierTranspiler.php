<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Identifier;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\CallFactory\VariableAssignmentCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class ElementIdentifierTranspiler implements TranspilerInterface
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

    public static function createTranspiler(): ElementIdentifierTranspiler
    {
        return new ElementIdentifierTranspiler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            VariableAssignmentCallFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof ElementIdentifierInterface;
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
        if (!$model instanceof ElementIdentifierInterface) {
            throw new NonTranspilableModelException($model);
        }

        return $this->variableAssignmentCallFactory->createForElementCollection(
            $model,
            VariableAssignmentCallFactory::createElementLocatorPlaceholder(),
            VariableAssignmentCallFactory::createCollectionPlaceholder()
        );
    }
}
