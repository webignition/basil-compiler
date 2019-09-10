<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\EnvironmentValueInterface;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;

class EnvironmentParameterValueTranspiler implements TranspilerInterface
{
    public static function createTranspiler(): EnvironmentParameterValueTranspiler
    {
        return new EnvironmentParameterValueTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof EnvironmentValueInterface;
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
        if ($this->handles($model) && $model instanceof EnvironmentValueInterface) {
            $variablePlaceholders = new VariablePlaceholderCollection();
            $environmentVariableArrayPlaceholder = $variablePlaceholders->create(
                VariableNames::ENVIRONMENT_VARIABLE_ARRAY
            );

            $content = sprintf(
                (string) $environmentVariableArrayPlaceholder . '[\'%s\']',
                $model->getObjectProperty()
            );

            return new TranspilationResult(
                [$content],
                new UseStatementCollection(),
                $variablePlaceholders
            );
        }

        throw new NonTranspilableModelException($model);
    }
}
