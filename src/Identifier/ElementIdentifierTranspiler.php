<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Identifier;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class ElementIdentifierTranspiler implements TranspilerInterface
{
    private $domCrawlerNavigatorCallFactory;

    public function __construct(DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory)
    {
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
    }

    public static function createTranspiler(): ElementIdentifierTranspiler
    {
        return new ElementIdentifierTranspiler(
            DomCrawlerNavigatorCallFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof ElementIdentifierInterface) {
            return false;
        }

        return $model->getValue() instanceof LiteralValueInterface;
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

        return $this->domCrawlerNavigatorCallFactory->createFindCallForIdentifier($model);
    }
}
