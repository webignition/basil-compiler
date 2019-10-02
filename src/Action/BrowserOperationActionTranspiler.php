<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\NoArgumentsAction;
use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;

class BrowserOperationActionTranspiler implements TranspilerInterface
{
    const HANDLED_ACTION_TYPES = [
        ActionTypes::BACK,
        ActionTypes::FORWARD,
        ActionTypes::RELOAD,
    ];

    public static function createTranspiler(): BrowserOperationActionTranspiler
    {
        return new BrowserOperationActionTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof NoArgumentsAction && in_array($model->getType(), self::HANDLED_ACTION_TYPES);
    }

    /**
     * @param object $model
     *
     * @return CompilableSourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): CompilableSourceInterface
    {
        if (!$model instanceof NoArgumentsAction) {
            throw new NonTranspilableModelException($model);
        }

        if (!in_array($model->getType(), self::HANDLED_ACTION_TYPES)) {
            throw new NonTranspilableModelException($model);
        }

        $variablePlaceholders = new VariablePlaceholderCollection();
        $pantherCrawlerPlaceholder = $variablePlaceholders->create(VariableNames::PANTHER_CRAWLER);
        $pantherClientPlaceholder = $variablePlaceholders->create(VariableNames::PANTHER_CLIENT);

        return new CompilableSource(
            [
                sprintf(
                    '%s = %s->%s()',
                    $pantherCrawlerPlaceholder,
                    $pantherClientPlaceholder,
                    $model->getType()
                ),
            ],
            new ClassDependencyCollection(),
            $variablePlaceholders,
            new VariablePlaceholderCollection()
        );
    }
}
