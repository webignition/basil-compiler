<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class EnvironmentParameterValueTranspiler implements TranspilerInterface
{
    private const MAPPED_VALUE = '$_ENV[\'%s\']';

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

    public function transpile(object $model, array $variableIdentifiers = []): string
    {
        if ($this->handles($model) && $model instanceof ObjectValueInterface) {
            return sprintf(self::MAPPED_VALUE, $model->getObjectProperty());
        }

        throw new NonTranspilableModelException($model);
    }
}
