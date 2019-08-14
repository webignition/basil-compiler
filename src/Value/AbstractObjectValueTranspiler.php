<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilTranspiler\UnknownObjectPropertyException;

abstract class AbstractObjectValueTranspiler implements ValueTypeTranspilerInterface
{
    abstract protected function getTranspiledValueMap(): array;

    public static function createTranspiler(): ValueTypeTranspilerInterface
    {
        return new static();
    }

    /**
     * @param ValueInterface $value
     *
     * @return string|null
     *
     * @throws UnknownObjectPropertyException
     */
    public function transpile(ValueInterface $value): ?string
    {
        if (!$this->handles($value) || !$value instanceof ObjectValueInterface) {
            return null;
        }

        $transpiledValue = $this->getTranspiledValueMap()[$value->getObjectProperty()] ?? null;

        if (is_string($transpiledValue)) {
            return $transpiledValue;
        }

        throw new UnknownObjectPropertyException($value);
    }
}
