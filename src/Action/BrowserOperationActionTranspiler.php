<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\NoArgumentsAction;
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

        $variableDependencies = new VariablePlaceholderCollection();
        $pantherCrawlerPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CRAWLER);
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $compilationMetadata = (new CompilationMetadata())->withVariableDependencies($variableDependencies);

        return new CompilableSource(
            [
                sprintf(
                    '%s = %s->%s()',
                    $pantherCrawlerPlaceholder,
                    $pantherClientPlaceholder,
                    $model->getType()
                ),
            ],
            $compilationMetadata
        );
    }
}
