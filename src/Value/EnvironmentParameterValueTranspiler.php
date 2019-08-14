<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;

class EnvironmentParameterValueTranspiler implements ValueTypeTranspilerInterface
{
    private const MAPPED_VALUE = '$_ENV[\'%s\']';

    public static function createTranspiler(): ValueTypeTranspilerInterface
    {
        return new EnvironmentParameterValueTranspiler();
    }

    public function handles(ValueInterface $value): bool
    {
        if (!$value instanceof ObjectValueInterface) {
            return false;
        }

        if (ValueTypes::ENVIRONMENT_PARAMETER !== $value->getType()) {
            return false;
        }

        return ObjectNames::ENVIRONMENT === $value->getObjectName();
    }

    public function transpile(ValueInterface $value): ?string
    {
        if (!$this->handles($value) || !$value instanceof ObjectValueInterface) {
            return null;
        }

        return sprintf(self::MAPPED_VALUE, $value->getObjectProperty());
    }
}
