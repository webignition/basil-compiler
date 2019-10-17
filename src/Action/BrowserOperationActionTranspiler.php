<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
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
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): SourceInterface
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

        $compilationMetadata = (new Metadata())->withVariableDependencies($variableDependencies);

        return (new Source())
            ->withStatements([
                sprintf(
                    '%s = %s->%s()',
                    $pantherCrawlerPlaceholder,
                    $pantherClientPlaceholder,
                    $model->getType()
                ),
            ])
            ->withMetadata($compilationMetadata);
    }
}
