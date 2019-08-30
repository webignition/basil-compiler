<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Identifier;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
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

        $collectionPlaceholder = new VariablePlaceholder('COLLECTION');
        $variablePlaceholder = new VariablePlaceholder('ELEMENT');

        $domCrawlerNavigatorFindCall = $this->domCrawlerNavigatorCallFactory->createFindCallForIdentifier($model);
        $domCrawlerNavigatorFindCallLines = $domCrawlerNavigatorFindCall->getLines();

        $useStatements = $domCrawlerNavigatorFindCall->getUseStatements();
        $variablePlaceholders = $domCrawlerNavigatorFindCall->getVariablePlaceholders();
        $variablePlaceholders = $variablePlaceholders->withAdditionalItems([
            $collectionPlaceholder,
            $variablePlaceholder,
        ]);

        $collectionAssignmentLine = sprintf(
            '%s = %s',
            (string) $collectionPlaceholder,
            array_pop($domCrawlerNavigatorFindCallLines)
        );

        $variableAssignmentLine = sprintf(
            '%s = %s',
            (string) $variablePlaceholder,
            $collectionPlaceholder . '->get(0)'
        );

        $lines = array_merge($domCrawlerNavigatorFindCallLines, [
            $collectionAssignmentLine,
            $variableAssignmentLine,
        ]);


        return new TranspilationResult($lines, $useStatements, $variablePlaceholders);
    }
}
