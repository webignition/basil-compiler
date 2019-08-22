<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\UnknownObjectPropertyException;

abstract class AbstractObjectValueTranspiler implements TranspilerInterface
{
    abstract protected function getTranspiledValueMap(array $variableNames = []): array;

    /**
     * @param object $model
     * @param array $variableNames
     *
     * @return string
     *
     * @throws NonTranspilableModelException
     * @throws UnknownObjectPropertyException
     */
    public function transpile(object $model, array $variableNames = []): string
    {
        if ($this->handles($model) && $model instanceof ObjectValueInterface) {
            $transpiledValue = $this->getTranspiledValueMap($variableNames)[$model->getObjectProperty()] ?? null;

            if (is_string($transpiledValue)) {
                return $transpiledValue;
            }

            throw new UnknownObjectPropertyException($model);
        }

        throw new NonTranspilableModelException($model);
    }
}
