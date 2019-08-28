<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;
use webignition\BasilTranspiler\Model\VariablePlaceholder;

class EnvironmentParameterValueTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): EnvironmentParameterValueTranspiler
    {
        return new EnvironmentParameterValueTranspiler();
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof ObjectValueInterface) {
            return false;
        }

        if (ValueTypes::ENVIRONMENT_PARAMETER !== $model->getType()) {
            return false;
        }

        return ObjectNames::ENVIRONMENT === $model->getObjectName();
    }

    public function transpile(object $model): TranspilationResult
    {
        if ($this->handles($model) && $model instanceof ObjectValueInterface) {
            $content = sprintf(
                (string) new VariablePlaceholder(VariableNames::ENVIRONMENT_VARIABLE_ARRAY) . '[\'%s\']',
                $model->getObjectProperty()
            );

            return new TranspilationResult(
                [$content],
                new UseStatementCollection(),
                new VariablePlaceholderCollection()
            );
        }

        throw new NonTranspilableModelException($model);
    }
}
