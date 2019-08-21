<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\UnknownObjectPropertyException;

abstract class AbstractObjectValueTranspiler implements TranspilerInterface
{
    abstract protected function getTranspiledValueMap(): array;

    /**
     * @param object $model
     *
     * @return string|null
     *
     * @throws UnknownObjectPropertyException
     */
    public function transpile(object $model): ?string
    {
        if (!$this->handles($model) || !$model instanceof ObjectValueInterface) {
            return null;
        }

        $transpiledValue = $this->getTranspiledValueMap()[$model->getObjectProperty()] ?? null;

        if (is_string($transpiledValue)) {
            return $transpiledValue;
        }

        throw new UnknownObjectPropertyException($model);
    }
}
